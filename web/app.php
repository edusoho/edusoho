<?php

use Symfony\Component\HttpFoundation\Request;

$loader = require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/bootstrap.php.cache';

$kernel = new AppKernel('prod', false);
//$kernel->loadClassCache();
$request = Request::createFromGlobals();
$kernel->setRequest($request);
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
