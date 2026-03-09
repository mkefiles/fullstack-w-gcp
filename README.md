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

## Code Steps
Coming soon...