<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

$app = new Silex\Application();

$app->view(function (array $controllerResult, Request $request) use ($app) {
    return new JsonResponse($controllerResult);
});


$api = $app['controllers_factory'];

$app->mount('/api', $api);

$api->get('/hello/{name}', function ($name) use ($app) {
	return 'xxx';
});

$app->run();