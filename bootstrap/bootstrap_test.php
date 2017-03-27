<?php

use Symfony\Component\HttpFoundation\Request;

use Codeages\Biz\Framework\UnitTests\UnitTestsBootstrap;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\AppConnectionFactory;

$loader = require __DIR__.'/../app/autoload.php';

// boot kernel
$request = Request::createFromGlobals();
$kernel = new AppKernel('test', true);
$kernel->setRequest($request);
$kernel->boot();

// inject request service
$container = $kernel->getContainer();
$container->enterScope('request');
$container->set('request', $request, 'request');

// boot test
$biz = $kernel->getContainer()->get('biz');
$bootstrap = new UnitTestsBootstrap($biz);
$bootstrap->boot();

// init service kernel env
ServiceKernel::instance()
    ->setEnvVariable(array(
        'host'          => 'test.com',
        'schemeAndHost' => 'http://test.com'));
