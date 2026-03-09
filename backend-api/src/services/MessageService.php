<?php

namespace App\services;

use App\dtos\Message;
use App\middleware\KafkaBridge;

class MessageService {

	/**
	 * The type is left out intentionally so to help
	 * ensure that this 'dependency' could be flexible
	 * @var KafkaBridge
	 */
	private $kafka_bridge;

	public function __construct(KafkaBridge $kafka_bridge) {
		$this->kafka_bridge = $kafka_bridge;
	}


	public function verifyInput(Message $message, array|null $payload) : bool {
		// DESC: Set DTO 'message' to received value (empty string if null)
		$message->setMessage($payload['message'] ?? "");

		// DESC: Check for errors
		// NOTE: An empty `$errors` array indicates that no validation errors
		// ... were found
		if (empty($message->getErrors())) {
			return true;
		}

		return false;
	}

	public function sendMessage(string $message) : string {
		return $this->kafka_bridge->produceMessage($message);
	}

}