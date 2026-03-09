<?php

use Slim\App;
use App\controllers\ApiController;

/**
 * Handle all of the routes, which each communicates with
 * the respective Controller class / method
 */
return function (App $app) : void {
	$app->get("/", [ApiController::class, "getIntroduction"]);

	$app->post("/producer", [ApiController::class, "produceMessage"]);

	$app->map(
		['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}',
		[ApiController::class, "catchAllForPageNotFound"]
	);
};

