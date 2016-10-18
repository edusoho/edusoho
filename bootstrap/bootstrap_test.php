<?php

use Symfony\Component\HttpFoundation\Request;
use Codeages\Biz\Framework\UnitTests\UnitTestsBootstrap;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Common\TestCaseConnection;

$loader = require __DIR__ . '/../app/autoload.php';

$kernel  = new AppKernel('test', true);
$request = Request::createFromGlobals();

$kernel->setRequest($request);
$kernel->boot();
$container = $kernel->getContainer();
$container->enterScope('request');
$container->set('request', $request, 'request');
$connection = $kernel->getContainer()->get('database_connection');
ServiceKernel::instance()
    ->setEnvVariable(array(
        'host'          => 'test.com',
        'schemeAndHost' => 'http://test.com'))
    ->setConnection(new TestCaseConnection($connection));