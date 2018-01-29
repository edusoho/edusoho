<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$xapi = $app['controllers_factory'];

$xapi->get('/play', function (Application $app, Request $request) {
    return $app->json(array('player' => 'video'));
});

$app->mount('/js/v1', $xapi);
