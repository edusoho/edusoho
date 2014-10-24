<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;

fix_gpc_magic();

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1'))
) {
    // header('HTTP/1.0 403 Forbidden');
    // exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
Debug::enable();

require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();

$kernel->boot();

// START: init service kernel
$serviceKernel = ServiceKernel::create($kernel->getEnvironment(), $kernel->isDebug());
$serviceKernel->setEnvVariable(array(
    'host' => $request->getHttpHost(),
    'schemeAndHost' => $request->getSchemeAndHttpHost(),
    'basePath' => $request->getBasePath(),
    'baseUrl' =>  $request->getSchemeAndHttpHost() . $request->getBasePath(),
));

$serviceKernel->setParameterBag($kernel->getContainer()->getParameterBag());
$serviceKernel->setConnection($kernel->getContainer()->get('database_connection'));
$serviceKernel->getConnection()->exec('SET NAMES UTF8');

$currentUser = new CurrentUser();
$currentUser->fromArray(array(
    'id' => 0,
    'nickname' => '游客',
    'currentIp' =>  $request->getClientIp(),
    'roles' => array(),
));
$serviceKernel->setCurrentUser($currentUser);
// END: init service kernel

// NOTICE: 防止请求捕捉失败而做异常处理 
// 包括：数据库连接失败等
try {
	$response = $kernel->handle($request);
} catch (\RuntimeException $e) {
    echo "Error!  ". $e->getMessage();
    die();
}

$response->send();
$kernel->terminate($request, $response);

function _fix_gpc_magic(&$item) {
  if (is_array($item)) {
    array_walk($item, '_fix_gpc_magic');
  }
  else {
    $item = stripslashes($item);
  }
}

function _fix_gpc_magic_files(&$item, $key) {
  if ($key != 'tmp_name') {
    if (is_array($item)) {
      array_walk($item, '_fix_gpc_magic_files');
    }
    else {
      $item = stripslashes($item);
    }
  }
}

function fix_gpc_magic() {
  if (ini_get('magic_quotes_gpc')) {
    array_walk($_GET, '_fix_gpc_magic');
    array_walk($_POST, '_fix_gpc_magic');
    array_walk($_COOKIE, '_fix_gpc_magic');
    array_walk($_REQUEST, '_fix_gpc_magic');
    array_walk($_FILES, '_fix_gpc_magic_files');
  }
}