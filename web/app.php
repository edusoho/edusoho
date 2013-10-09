<?php

if (!file_exists(__DIR__ . '/../app/data/install.lock')) {
	header("Location: install/install.php");
	exit(); 
}

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

// Use APC for autoloading to improve performance.
// Change 'sf2' to a unique prefix in order to prevent cache key conflicts
// with other applications also using APC.
/*
$loader = new ApcClassLoader('sf2', $loader);
$loader->register(true);
*/

require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();

$kernel->boot();

// START: init service kernel
$serviceKernel = ServiceKernel::create($kernel->getEnvironment(), $kernel->isDebug());
$serviceKernel->setParameterBag($kernel->getContainer()->getParameterBag());
$serviceKernel->setConnection($kernel->getContainer()->get('database_connection'));

$currentUser = new CurrentUser();
$currentUser->fromArray(array(
    'id' => 0,
    'nickname' => '游客',
    'currentIp' =>  $request->getClientIp()
));
$serviceKernel->setCurrentUser($currentUser);
// END: init service kernel

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
