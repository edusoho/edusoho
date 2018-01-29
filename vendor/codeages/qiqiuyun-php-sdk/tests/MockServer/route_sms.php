<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$sms = $app['controllers_factory'];

$sms->post('/messages', function (Application $app, Request $request) {
    $params = $request->request->all();
    if ($params['mobile'] == 'error')
    {
        return $app->json(array('error' => array(
            'code' => 6,
            'message' => 'Service unavailable.',
            'trace_id' => '1516156112_40937211d8',

        ))); 
    }
    return $app->json(array('status' => 'success', 'sn' => 'S2017122709161354269'));
});

$sms->post('/messages/batch_messages', function (Application $app, Request $request) {
    $params = $request->request->all();
    if ($params['mobile'] == 'error')
    {
        return $app->json(array('error' => array(
            'code' => 6,
            'message' => 'Service unavailable.',
            'trace_id' => '1516156112_40937211d8',

        ))); 
    }
    return $app->json(array('status' => 'success', 'sn' => 'S2017122709161354270'));
});

$app->mount('/', $sms);
