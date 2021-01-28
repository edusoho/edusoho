<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Biz\UnitTests\UnitTestsBootstrap;

$loader = require __DIR__.'/../app/autoload.php';
if (PHP_VERSION_ID >= 70400) {
    error_reporting('E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED');
}
// boot kernel
$request = Request::createFromGlobals();
$kernel = new AppKernel('test', true);
$kernel->setRequest($request);
//clear cache
$filesystem = new \Symfony\Component\Filesystem\Filesystem();
$filesystem->remove($kernel->getCacheDir());

$kernel->boot();


/**
 * expired in symfony 3.0
 */
//$container->enterScope('request');
//$container->set('request', $request, 'request');

$kernel->getContainer()->get('request_stack')->push($request);
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
