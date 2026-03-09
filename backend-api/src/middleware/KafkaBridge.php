<?php

declare(strict_types=1);

namespace App\middleware;

use Exception;
use RdKafka\Conf as Configuration;
use RdKafka\Producer as Producer;
use RdKafka\ProducerTopic as ProducerTopic;
use RdKafka\TopicConf as TopicConf;

/**
 * By providing the necessary constructor values, this will complete
 * all necessary configuration steps for the Kafka instance to
 * be created. Then you can simply call `produceMessage()` to
 * send the message into the Kafka instance
 */
class KafkaBridge {
	private string $client_id;
	private string $queue_buffering_max_ms;
	private string $queue_buffering_max_messages;
	private string $broker_name_port;
	private string $message_timeout_ms;
	private string $topic_name;
	private Configuration $configuration;
	private Producer $producer;
	private TopicConf $topic_conf;
	private ProducerTopic $producer_topic;

	private string $delivery_report_response;

	public function __construct(
		$client_id, $queue_buffering_max_ms, $queue_buffering_max_messages,
		$broker_name_port, $message_timeout_ms, $topic_name
	) {
		// DESC: Assign parameters to instance
		$this->client_id = $client_id;
		$this->queue_buffering_max_ms = $queue_buffering_max_ms;
		$this->queue_buffering_max_messages = $queue_buffering_max_messages;
		$this->broker_name_port = $broker_name_port;
		$this->message_timeout_ms = $message_timeout_ms;
		$this->topic_name = $topic_name;

		// DESC: Initialize Kafka (incl. configurations, Producer and Topic)
		$this->configuration = new Configuration();
		$this->setGlobalConfigurations();
		$this->producer = new Producer($this->configuration);
		$this->setProducerConfigurations();
		$this->topic_conf = new TopicConf();
		$this->setTopicConfigurations();
	}

	private function setGlobalConfigurations() : void {
		$this->configuration->set("client.id", $this->client_id);
		$this->configuration->set("queue.buffering.max.ms", $this->queue_buffering_max_ms);
		$this->configuration->set("queue.buffering.max.messages", $this->queue_buffering_max_messages);
		$this->configuration->setDrMsgCb([$this, "deliveryReportCallback"]);
	}

	private function deliveryReportCallback($kafka_instance, $kafka_message) : void {
		if ($kafka_message->err) {
			$this->delivery_report_response = $kafka_message->err;
		} else {
			$this->delivery_report_response = "Delivered";
		}
	}

	private function setProducerConfigurations() : void {
		$this->producer->addBrokers($this->broker_name_port);
	}

	private function setTopicConfigurations() : void {
		$this->topic_conf->set("message.timeout.ms", $this->message_timeout_ms);
		$this->producer_topic = $this->producer->newTopic($this->topic_name, $this->topic_conf);
	}

	public function produceMessage(string $message) : string {
		try {
			$this->producer_topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
			// NOTE: The `poll` function triggers all callback functions
			$this->producer->poll(-1);
			return $this->delivery_report_response;
		} catch (Exception $exception) {
			echo $this->delivery_report_response;
			return "Caught Exception:\n" . $exception->getMessage();
		}
	}

}