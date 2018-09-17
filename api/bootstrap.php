<?php

date_default_timezone_set('UTC');

$loader = require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Annotations\AnnotationRegistry;

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

define('RUNTIME_ENV', 'API');
define('ROOT_DIR', __DIR__.DIRECTORY_SEPARATOR.'/../app');

if (API_ENV == 'prod') {
    $kernel = new AppKernel('prod', false);
} else {
    $kernel = new AppKernel('dev', true);
}

$kernel->loadClassCache();
$request = Request::createFromGlobals();
$kernel->setRequest($request);
$kernel->boot();

$parameters = include __DIR__.'/config/paramaters.php';
if (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']) {
    $parameters['host'] = 'https://'.$_SERVER['HTTP_HOST'];
} else {
    $parameters['host'] = 'http://'.$_SERVER['HTTP_HOST'];
}
