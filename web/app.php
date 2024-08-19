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

if (isOldApiCall()) {
    define('API_ENV', 'prod');
    include __DIR__.'/../api/index.php';
    exit();
}

use Symfony\Component\HttpFoundation\Request;

$loader = require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/bootstrap.php.cache';

$kernel = new AppKernel('prod', false);
//$kernel->loadClassCache();
$request = Request::createFromGlobals();


if (file_exists(__DIR__.'/../app/config/proxy.php')) {
    $ips = require_once __DIR__.'/../app/config/proxy.php';
    // @see https://symfony.com/doc/3.x/deployment/proxies.html
    Request::setTrustedProxies(
        $ips,
        Request::HEADER_X_FORWARDED_ALL
    );
}

$kernel->setRequest($request);
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

function isOldApiCall()
{
    return (!(isset($_SERVER['HTTP_ACCEPT']) && 'application/vnd.edusoho.v2+json' == $_SERVER['HTTP_ACCEPT']))
        && ((0 === strpos($_SERVER['REQUEST_URI'], '/api')) || (0 === strpos($_SERVER['REQUEST_URI'], '/app.php/api')));
}
