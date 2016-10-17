<?php

use Symfony\Component\HttpFoundation\Request;
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
$biz = $kernel->getContainer()->get('biz');

ServiceKernel::instance()
    ->setEnvVariable(array(
        'host'          => 'test.com',
        'schemeAndHost' => 'http://test.com'))
    ->setConnection(new TestCaseConnection($biz['db']));