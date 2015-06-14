<?php
date_default_timezone_set('UTC');

require_once __DIR__.'/../vendor/autoload.php';


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\HttpFoundation\ParameterBag;
// use Symfony\Component\Debug\Debug;

// Debug::enable();

$config = include __DIR__ . '/config.php';

$connection = DriverManager::getConnection(array(
    'dbname' => $config['database_name'],
    'user' => $config['database_user'],
    'password' => $config['database_password'],   
    'host' => $config['database_host'],
    'driver' => $config['database_driver'],
    'charset' => $config['database_charset'],
));

// var_dump($config);

$serviceKernel = ServiceKernel::create('dev', true);
$serviceKernel->setParameterBag(new ParameterBag($config));
$serviceKernel->setConnection($connection);
// $serviceKernel->getConnection()->exec('SET NAMES UTF8');

$currentUser = new CurrentUser();
$currentUser->fromArray(array(
    'id' => 0,
    'nickname' => '游客',
    'currentIp' =>  '',
    'roles' => array(),
));
$serviceKernel->setCurrentUser($currentUser);

include __DIR__ . '/src/functions.php';


$app = new Silex\Application();

$app['debug'] = true;

$app->view(function (array $result, Request $request) use ($app) {
    return new JsonResponse($result);
});

$app->mount('/api/users', include __DIR__ . '/src/users.php' );
$app->mount('/api/me', include __DIR__ . '/src/me.php' );
$app->mount('/api/courses', include __DIR__ . '/src/courses.php' );

$app->run();