<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use Codeages\Biz\Framework\UnitTests\UnitTestsBootstrap;
use Topxia\Service\Common\ServiceKernel;

$loader = require __DIR__.'/../app/autoload.php';

$request = Request::createFromGlobals();
$kernel = new AppKernel('test', true);
$kernel->setRequest($request);
$kernel->boot();

$biz = $kernel->getContainer()->get('biz');

$bootstrap = new UnitTestsBootstrap($biz);
$bootstrap->boot();


$container = $kernel->getContainer();
$container->enterScope('request');
$container->set('request', $request, 'request');
ServiceKernel::instance()
    ->setEnvVariable(array(
        'host'          => 'test.com',
        'schemeAndHost' => 'http://test.com'))
    ->setConnection($biz['db']);