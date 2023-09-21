<?php

if (!file_exists(__DIR__.'/../app/data/install.lock')) {
    header('Location: install/install.php');
    exit();
}

if ((0 !== strpos($_SERVER['REQUEST_URI'], '/admin')) && file_exists(__DIR__.'/../app/data/upgrade.lock')) {
    $time = file_get_contents(__DIR__.'/../app/data/upgrade.lock');
    date_default_timezone_set('Asia/Shanghai');
    $currentTime = time();
    if ($currentTime <= (int) $time) {
        header('Content-Type: text/html; charset=utf-8');
        echo file_get_contents(__DIR__.'/../app/Resources/TwigBundle/views/Exception/upgrade-info.html');
        exit();
    }
}

if ((0 === strpos($_SERVER['REQUEST_URI'], '/admin/setting/developer')) || (0 === strpos($_SERVER['REQUEST_URI'], '/app.php/admin/setting/developer')) ||
    (0 === strpos($_SERVER['REQUEST_URI'], '/admin/v2/setting/developer')) || (0 === strpos($_SERVER['REQUEST_URI'], '/app.php/admin/v2/setting/developer'))) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this link.');
}

require __DIR__.'/../app/security.php';

define('APP_ENVIRONMENT', 'prod');

if (isOldApiCall(APP_ENVIRONMENT)) {
    define('API_ENV', 'prod');
    include __DIR__.'/../api/index.php';
    exit();
}

use Symfony\Component\HttpFoundation\Request;

fix_gpc_magic();

$loader = require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/bootstrap.php.cache';

$kernel = new AppKernel(APP_ENVIRONMENT, false);
//$kernel->loadClassCache();
$request = Request::createFromGlobals();
$kernel->setRequest($request);
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
