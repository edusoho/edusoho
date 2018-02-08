<?php

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if ('cli-server' === php_sapi_name() && is_file($filename)) {
    return false;
}

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

require __DIR__.'/route_xapi.php';
require __DIR__.'/route_play.php';
require __DIR__.'/route_sms.php';

$app->run();
