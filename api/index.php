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
$config         = include __DIR__.'/config.php';
$config['host'] = 'http://'.$_SERVER['HTTP_HOST'];

$connection = DriverManager::getConnection(array(
    'wrapperClass' => 'Topxia\Service\Common\Connection',
    'dbname'       => $config['database_name'],
    'user'         => $config['database_user'],
    'password'     => $config['database_password'],
    'host'         => $config['database_host'],
    'driver'       => $config['database_driver'],
    'charset'      => 'utf8'
));

$serviceKernel = ServiceKernel::create($config['environment'], true);
$serviceKernel->setParameterBag(new ParameterBag($config));
$serviceKernel->setConnection($connection);
// $serviceKernel->getConnection()->exec('SET NAMES UTF8');

include __DIR__.'/src/functions.php';

$app = new Silex\Application();
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
}

);

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

include __DIR__.'/config/routing.php';

$app->run();
