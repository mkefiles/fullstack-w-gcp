<?php

declare(strict_types=1);

use \DI\Bridge\Slim\Bridge as Bridge;
use \DI\ContainerBuilder as ContainerBuilder;
use \Psr\Http\Message\ServerRequestInterface as ServerRequest;
use \Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';

// DESC: Create and configure the DI Container
// NOTE: This is necessary so the applicable dependencies
// ... can be injected upfront (PHP-DI: PHP Definitions)
// NOTE: This essentially creates an instance of KafkaBridge
// ... that any class can use (i.e., a singleton) [see MessageService]
$container_builder = new ContainerBuilder();
$container_builder->addDefinitions(__DIR__ . '/../src/configurations/kafka_configs.php');
$container = $container_builder->build();

// DESC: Create the application
// NOTE: This relies on PHP-DI (and the `Bridge` class)
// ... in lieu of PHP Slim (and the `AppFactory` class)
$app = Bridge::create($container);

// DESC: Adds all app routes to an external file
// NOTE: The file works by using a function that receives
// ... the `$app` as the one parameter
$routes = require __DIR__ . "/../src/routes.php";
$routes($app);

// DESC: Changes base from 'localhost/' to 'localhost/api/'
$app->setBasePath('/api');

// DESC: Add middleware
// NOTE: This handles routing, parsing of JSON / XML and errors /
// ... exceptions caused by middlewares
// NOTE: `addErrorMiddleware()` must be added last
// FIXME: Set $displayErrorDetails to `false` in Prod
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

// DESC: Allow for CORS
$app->add(function (ServerRequest $request, RequestHandler $handler) use ($app): Response {
	if($request->getMethod() === 'OPTIONS') {
		$response = $app->getResponseFactory()->createResponse();
	} else {
		$response = $handler->handle($request);
	}

	$response = $response
		->withHeader('Access-Control-Allow-Credentials', 'true')
		->withHeader('Access-Control-Allow-Origin', '*')
		->withHeader('Access-Control-Allow-Headers', '*')
		->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
		->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
		->withHeader('Pragma', 'no-cache');

	if (ob_get_contents()) {
		ob_clean();
	}

	return $response;
});

$app->run();
