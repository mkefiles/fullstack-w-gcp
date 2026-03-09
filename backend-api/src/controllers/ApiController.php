<?php

declare(strict_types=1);

namespace App\controllers;

use App\dtos\Message;
use App\services\MessageService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * ApiController is, through PHP-DI, being used as a
 * "Controller as a Service", which more closely resembles
 * what Java Spring Boot does
 */
class ApiController {

	/**
	 * The type is left out intentionally so to help
	 * ensure that this 'dependency' could be flexible
	 * @var MessageService
	 */
	private $message_service;

	public function __construct(MessageService $message_service) {
		$this->message_service = $message_service;
	}

	/**
	 * Introduction message for website.
	 *
	 * @link GET /api/
	 * @return Response
	 */
	public function getIntroduction(Request $request, Response $response): Response {
		$response->getBody()->write("Success!");
		return $response;
	}

	/**
	 * The Kafka Producer endpoint that messages will
	 * be passed to / through
	 *
	 * @link POST /api/producer
	 * @return Response
	 */
	public function produceMessage(Request $request, Response $response): Response {
		$data = $request->getParsedBody();
		$dto = new Message();

		$is_valid_input = $this->message_service->verifyInput($dto, $data);

		if ($is_valid_input) {
			$service_response = $this->message_service->sendMessage($dto->getMessage());
			if (str_contains($service_response, "Delivered")) {
				$response->getBody()->write("Successful");
				return $response->withStatus(201);
			} elseif (str_contains($service_response, "Caught Exception")) {
				$response->getBody()->write("Encountered Exception:\n" . $service_response);
				return $response->withStatus(500);
			} else {
				$response->getBody()->write("Producer Error:\n" . $service_response);
				return $response->withStatus(500);
			}
		} else {
			$response->getBody()->write(json_encode($dto->getErrors()));
			return $response
				->withStatus(400)
				->withHeader('Content-Type', 'application/json');
		}
	}

	/**
	 * Catch-all response for undefined /api/* requests
	 *
	 * @link GET, POST, PUT, DELETE and PATCH /api/*
	 * @return Response
	 */
	public function catchAllForPageNotFound(Request $request, Response $response): Response {
		$response->getBody()->write("Page Not Found");
		return $response->withStatus(404);
	}

}