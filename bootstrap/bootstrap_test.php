<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Codeages\Biz\Framework\UnitTests\UnitTestsBootstrap;

$loader = require __DIR__.'/../app/autoload.php';

// boot kernel
$request = Request::createFromGlobals();
$kernel = new AppKernel('test', true);
$kernel->setRequest($request);
$kernel->boot();

//clear cache
$filesystem = new \Symfony\Component\Filesystem\Filesystem();
$filesystem->remove($kernel->getCacheDir());

// inject request service
$container = $kernel->getContainer();
$container->enterScope('request');
$container->set('request', $request, 'request');

// boot test
$biz = $kernel->getContainer()->get('biz');
$bootstrap = new UnitTestsBootstrap($biz);
$bootstrap->boot();

\Biz\BaseTestCase::setAppKernel($kernel);
\Biz\BaseTestCase::setDb($biz['db']);

if (isset($biz['redis'])) {
    \Biz\BaseTestCase::setRedis($biz['redis']);
}

// init service kernel env
ServiceKernel::instance()
    ->setEnvVariable(array(
        'host' => 'test.com',
        'schemeAndHost' => 'http://test.com',
        'basePath' => '/',
        'baseUrl' => 'http://test.com/',
    ));
