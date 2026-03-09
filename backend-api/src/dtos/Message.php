<?php
declare(strict_types=1);

namespace App\dtos;

use App\utilities\InputValidation;

class Message {
	private string $message;
	private array $errors;

	// DESC: No-args Constructor
	public function __construct() {
		// DESC: Initialize $errors for API access
		$this->errors = [];
	}

	// DESC: Getter and Setter methods
	/**
	 * @return string
	 */
	public function getMessage(): string {
		return $this->message;
	}

	/**
	 * Sanitizes input (w/ htmlspecialchars()), checks if length
	 * is valid and assigns value to Object.
	 *
	 * In the event that the length is invalid, an error message
	 * is added to the `$errors` array.
	 *
	 * @param string $message
	 */
	public function setMessage(string $message): void {
		// DESC: Immediately sanitize input
		$input = htmlspecialchars($message);

		// DESC: Validate length
		if (InputValidation::is_length_valid($input, 10, 140) === false) {
			$this->errors['message'] = "Message must be between 10 and 140 characters";
		}

		$this->message = $input;
	}
	public function getErrors(): array {
		return $this->errors;
	}
}