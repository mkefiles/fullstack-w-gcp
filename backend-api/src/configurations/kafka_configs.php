<?php

use App\middleware\KafkaBridge as KafkaBridge;
use function DI\create;
use function DI\get;

return [
	// DESC: Define necessary constructor values
	// NOTE: Kafka Producer Configurations
	"client.id" => "php-application",

	// FIXME: Comment / uncomment applicable Bootstrap Server for DEV / PROD
	// "bootstrap.server" => "kafka-broker:29092",
	"bootstrap.server" => "10.128.0.2:9092",

	// NOTE: Kafka Broker Configurations
	"log.retention.hours" => "1",

	// NOTE: LibRdKafka Configurations
	"queue.buffering.max.ms" => "1",
	"queue.buffering.max.messages" => "1",
	"message.timeout.ms" => "1000",

	// NOTE: Custom Configurations
	"topic_name" => "frontend-messages",

	// DESC: Define creating the class (incl. params)
	KafkaBridge::class => create(KafkaBridge::class)->constructor(
		get("client.id"), get("queue.buffering.max.ms"),
		get("queue.buffering.max.messages"), get("bootstrap.server"),
		get("message.timeout.ms"), get("topic_name")
	)
];