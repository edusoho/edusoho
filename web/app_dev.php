<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

$loader = require_once __DIR__.'/../app/autoload.php';
Debug::enable();

$kernel = new AppKernel('dev', true);
//$kernel->loadClassCache();
$request = Request::createFromGlobals();
$kernel->setRequest($request);
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);