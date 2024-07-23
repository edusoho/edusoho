<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.

if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || 'cli-server' === php_sapi_name())
) {
    if (!file_exists(__DIR__.'/../app/data/dev.lock')) {
        header('HTTP/1.0 403 Forbidden');
        exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
    }
}

if (isOldApiCall()) {
    define('API_ENV', 'dev');
    include __DIR__.'/../api/index.php';
    exit();
}

$loader = require_once __DIR__.'/../app/autoload.php';
Debug::enable();

$kernel = new AppKernel('dev', true);
//$kernel->loadClassCache();
$request = Request::createFromGlobals();
$kernel->setRequest($request);
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

function isOldApiCall()
{
    return (!(isset($_SERVER['HTTP_ACCEPT']) && 'application/vnd.edusoho.v2+json' == $_SERVER['HTTP_ACCEPT']))
    && ((0 === strpos($_SERVER['REQUEST_URI'], '/api')) || (0 === strpos($_SERVER['REQUEST_URI'], '/app_dev.php/api')));
}
