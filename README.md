# Full Stack Project



## About

This is a RESTful API that uses the following tech-stack:

- PHP Slim : for handling the backend API functionality
  - This project uses PHP-DI for *Dependency Injection*
- Apache Kafka : an event-streaming platform for processing real-time data-feeds
  - Kafbat UI is included to monitor Kafka
- React : for handling the frontend UI (with TypeScript)
- Google Cloud Platform:
  - Two *Cloud Run* instances for the PHP API and Kafbat
  - Two *Cloud Storage* instances for the React App and Kafbat Configuration
  - One *Compute Engine* instance to run Apache Kafka
  - One *Serverless VPC Access* instance to bridge access between *Cloud Run* and *Compute Engine*

In this example, there is no *Consumer* (in the technical sense). The API will act as the *Producer* and Kafbat UI will provide necessary insight as to whether the message did/not successfully post.



## Development / Production (using Docker)

This project is *containerized*, however there are differences between how it was used.



### Node

For the NodeJS / React / TypeScript development, Node was ran as a containerized interactive terminal:

```bash
# DESC: Create the Vite project
docker run -it --rm -v "$PWD":/app -w /app node:24.13.1-slim npm create vite@latest .

# DESC: Install dependencies
docker run -it --rm -v "$PWD":/app -w /app node:24.13.1-slim npm install

# DESC: Run the development server (with a Bind Mount)
docker run -it --rm -p 5173:5173 -v "$PWD":/app -w /app node:24.13.1-slim npm run dev

# DESC: Build the App (using Vite)
docker run -it --rm -p 5173:5173 -v "$PWD":/app -w /app node:24.13.1-slim npm run build
```

By doing this, a simple set of static files were created that I could easily upload to a *GCP Cloud Storage* instance.



### PHP-Apache

For the PHP-Apache development, a two-step process was used. For **Development**, a custom image was created using the *compose.yml* and a *Dockerfile*:

```yaml
services:
    php-backend:
    	container_name: php-backend
    	build:
    		context: ./backend-api
    		dockerfile: .docker/Dockerfile
    	ports:
    		- "80:80"
    	volumes:
    		- ./backend-api:/var/www/html
```



```dockerfile
FROM php:8.5-apache

LABEL authors="Mike Files"
LABEL maintainer="Mike Files"

# DESC: Update apt, upgrade system and install 'librdkafka'
# NOTE: This is placed early in the image-build process so
# ... to prevent the need to re-download every time
RUN apt-get update \
    && apt-get -y upgrade \
    && apt-get install -y --no-install-recommends librdkafka-dev

# DESC: Install PHP PIE build-toolchain
# NOTE: The installation of PHP PIE suggested a build-toolchain, which
# ... is what the prior command should install, however I removed the
# ... `php-dev` package because that was throwing an error
# NOTE: Installing 'git' is an additional requirement for installing
# ... 'rdkafka' with PIE so I appended it here
RUN apt-get install -y gcc make autoconf libtool bison re2c pkg-config git

# DESC: Move PHP PIE (v1.3.8) .phar file to image
COPY .docker/pie.phar /usr/local/bin/pie

# DESC: Make PHP PIE .phar executable
RUN chmod +x /usr/local/bin/pie

# DESC: Install 'rdkakfka' with PIE
RUN pie install rdkafka/rdkafka

# DESC: Set the working directory to Apache's base
WORKDIR /var/www/html/

# DESC: Overwrite the "000-default.conf" file
COPY .docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# DESC: Overwrite the "apache2.conf" file
COPY .docker/apache2.conf /etc/apache2/apache2.conf

# DESC: Overwrite the "php.ini" (for rdkafka extension)
COPY .docker/php.ini /usr/local/etc/php/conf.d/php.ini

# DESC: Enable mod_rewrite (rewrite module) and mod_actions (actions module)
RUN a2enmod rewrite && a2enmod actions
```

For **Production**, the **Development** image was used in the creation of a slightly modified image. Given that GCP requires *linux/amd64*, this image was updated to use `platform: linux/amd64`:

```yaml
services:
    php-backend-gcp:
        container_name: php-backend-gcp
        platform: linux/amd64
        build:
            context: ./backend-api
            dockerfile: .docker/Dockerfile
        ports:
        	- "80:80"
        volumes:
        	- ./backend-api:/var/www/html
```



```dockerfile
# DESC: Build PROD Image off of DEV
# NOTE: This should remove the need for re-downloading
# ... any required dependencies
FROM full-stack-php-backend-gcp:latest

# DESC: Set Image meta-data
LABEL authors="Mike Files"
LABEL maintainer="Mike Files"

# DESC: Update Apache Configuration
# NOTE: This appends the necessary ServerName configuration
# ... to the end of the 'apache2.conf' file
RUN echo "\n\nServerName www.mike-files.com" >> /etc/apache2/apache2.conf

# DESC: Copy project contents to Image
# NOTE: If the Dockerfile is accessed from the backends root
# ... directory (i.e, not a sub-directory within 'backend')
# ... then the following commands ensure that files from
# ... the backend-root <src> are copied to the <workdir>
WORKDIR /var/www/html/
COPY . .
```

By doing this, I was able to cut down on re-downloading dependencies and use what I had already created during Development. The Production image was uploaded to and used as a *GCP Cloud Run* instance ... mainly because there was no need for state.



### Kafbat UI

For the Kafbat UI instance, this was the easiest setup because I basically used the image as-is straight from Docker Hub. In **Development**, I brought the image down with some configuration tweaks:

```yaml
# NOTE: A U.I. for Kafka (use http://localhost:8080 to access)
# NOTE: When `DYNAMIC_CONFIG_ENABLED` is set to `true`, any changes
# ... made in the U.I. will save to the 'dynamic_config.yaml' (see
# ... the volume mounting config below)
services:
    kafka-ui:
        container_name: "kafka-ui"
        image: "kafbat/kafka-ui:main"
        ports:
        	- "8080:8080"
        depends_on: # Added for Kafka / Kafbat comm.'s
        	- "kafka-broker"
        environment:
            KAFKA_CLUSTERS_0_NAME: "local"
            KAFKA_CLUSTERS_0_BOOTSTRAPSERVERS: "kafka-broker:29092"
            KAFKA_CLUSTERS_0_METRICS_PORT: 9997
            DYNAMIC_CONFIG_ENABLED: "true"
        volumes:
        	- ./kafka-ui/config.yml:/etc/kafkaui/dynamic_config.yaml
```

For **Production**, I used the image directly and pushed to a *GCP Cloud Run* instance. Given that I wanted to used *Dynamic Configuration*, I needed a way to store the *dynamic_config.yaml* file, so I implemented a *GCP Cloud Storage* instance and connected it to the *GCP Cloud Run* for volume-mounting.



### Kafka

For Kafka, I used two entirely different methods. In **Development**, a *containerized* instance was used, however I did **not** *containerize* for **Production**. Given that Kafka needs *state* and it needs to be running at all times (especially with the prolonged start-up time), I was between using a *GCP Managed Service* or a *GCP Compute Engine* instance. With this not being a true *Production* application, I figured it was cheaper (not to mention more educational / fun) to use an `n1-standard-1` *Compute Engine* instance.

With the virtual machine being a blank slate, I had to do the following using terminal and VIM:

-   Install Java (OpenJDK 25.0.2) **and** Kafka (Kafka 2.13 - 4.1.1)
-   Decompress the binaries
-   Move them to their appropriate directory
    -   Java: */usr/lib/jvm*
    -   Kafka: */usr/local/kafka*
-   Update the Linu `PATH` for Java
-   Create a persistent directory for Kafka (by default it sets up pointing at a temporary directory)
-   Update the Kafka *server.properties* file for necessary configuration updates
-   Update the Kafka *kafka-server-start.sh* file for necessary Kafbat UI settings

Finally, I went through the process of creating a *SystemCTL* service to ensure that Kafka starts up automatically if / when the *Compute Engine* reboots:

```
[Unit]
Description=Initialize the Kafka Server on reboot and start-up
Requires=network-online.target
After=network-online.target

[Service]
Environment="JAVA_HOME=/usr/lib/jvm/jdk-25.0.2"
Type=simple
User=m_kefiles
ExecStart=/usr/local/kafka/kafka_2.13-4.1.1/bin/kafka-server-start.sh /usr/local/kafka/kafka_2.13-4.1.1/config/server.properties
ExecStop=/usr/local/kafka/kafka_2.13-4.1.1/bin/kafka-server-stop.sh

[Install]
WantedBy=default.target
```



## Using the Application

The application has been setup so it is accessible using my URL. Please use the following steps:

1.  Navigate to [Mike-Files](https://www.mike-files.com/)
2.  Click "Next" (it will be disabled until `:: API Connection: Success! ::`)
3.  Type the desired message and click "Submit"
4.  Click "Review Kafbat UI" (this redirects to the Kafbat UI URL provided by *GCP Cloud Run*)
5.  Click "Messages"



## Retrospect

If I were to have done this project again, I would have, likely, done the following:

-   Implemented a better *Development* to *Production* process:
    -   The React project requires a re-build when changing the URLs
    -   The PHP-Apache image requires a rebuild when changing the URLs
-   Focused more on security (with regards to GCP)
-   Used a different language for the backend API
    -   PHP was great, however the added need for tinkering with the server (mainly around the start of the project) could have been avoided had I used another language / library (e.g., Javalin)
-   Researching / learning GCP (or whatever cloud platform was used) **before** jumping straight in
    -   I wanted to get this completed as soon as possible so I jumped straight into GCP without having spent any time learning the platform, which caused a lot of trial-and-error work