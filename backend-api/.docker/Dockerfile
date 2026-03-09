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