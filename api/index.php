<?php
date_default_timezone_set('UTC');

require_once __DIR__.'/../vendor2/autoload.php';

use Doctrine\DBAL\DriverManager;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Api\ApiAuth;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
// use Symfony\Component\Debug\Debug;

// ErrorHandler::register(0);
// ExceptionHandler::register(false);
$paramaters         = include __DIR__.'/config/paramaters.php';
$paramaters['host'] = 'http://'.$_SERVER['HTTP_HOST'];

$connection = DriverManager::getConnection(array(
    'wrapperClass' => 'Topxia\Service\Common\Connection',
    'dbname'       => $paramaters['database_name'],
    'user'         => $paramaters['database_user'],
    'password'     => $paramaters['database_password'],
    'host'         => $paramaters['database_host'],
    'driver'       => $paramaters['database_driver'],
    'charset'      => 'utf8'
));

$serviceKernel = ServiceKernel::create($paramaters['environment'], true);
$serviceKernel->setParameterBag(new ParameterBag($paramaters));
$serviceKernel->setConnection($connection);

include __DIR__.'/src/functions.php';

$app = new Silex\Application();

include __DIR__.'/config/container.php';
include __DIR__.'/config/routing.php';

$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app->view(function (array $result, Request $request) use ($app) {
    // 兼容气球云搜索的接口
    $documentType = $request->headers->get('X-Search-Document');
    if ($documentType) {
        $class = "Topxia\\Api\\SpecialResponse\\{$documentType}Response";
        if (!class_exists($class)) {
            throw new \RuntimeException("{$documentType}Response不存在！");
        }

        $obj = new $class();
        $result = $obj->filter($result);
    }
    return new JsonResponse($result);
});

$app->before(function (Request $request) use ($app) {
    $auth = new ApiAuth(include __DIR__ . '/config/whitelist.php');
    $auth->auth($request);
});

$app->error(function (\Exception $e, $code) {
    return array(
        'code' => $code,
        'message' => $e->getMessage(),
    );
});

$app->run();