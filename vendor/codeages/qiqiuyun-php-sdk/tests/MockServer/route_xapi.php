<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$xapi = $app['controllers_factory'];

$xapi->post('/statements', function (Application $app, Request $request) {
    $object = $request->request->get('object');
    if ($object['id'] <= 0) {
        return $app->json(array(
            'error' => array(
                'code' => 9,
                'message' => 'invalid argument',
            ),
        ), 400);
    }

    return $app->json(array('success' => true));
});

$app->mount('/xapi', $xapi);
