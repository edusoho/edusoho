<?php
date_default_timezone_set('UTC');

require_once __DIR__.'/../vendor2/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
// use Symfony\Component\Debug\Debug;

// Debug::enable();
ErrorHandler::register();
ExceptionHandler::register();
$paramaters = include __DIR__ . '/config/paramaters.php';
$paramaters['host'] = 'http://'.$_SERVER['HTTP_HOST'];

$connection = DriverManager::getConnection(array(
    'dbname' => $paramaters['database_name'],
    'user' => $paramaters['database_user'],
    'password' => $paramaters['database_password'],   
    'host' => $paramaters['database_host'],
    'driver' => $paramaters['database_driver'],
    'charset' => 'utf8',
));

$serviceKernel = ServiceKernel::create($paramaters['environment'], true);
$serviceKernel->setParameterBag(new ParameterBag($paramaters));
$serviceKernel->setConnection($connection);
// $serviceKernel->getConnection()->exec('SET NAMES UTF8');


include __DIR__ . '/src/functions.php';


$app = new Silex\Application();
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app['debug'] = true;

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

include __DIR__ . '/config/container.php';

$app->before(function (Request $request) use ($app) {

    $path = rtrim($request->getPathInfo(), '/');

    $whilelist = include __DIR__ . '/config/whitelist.php';

    $inWhiteList = 0;
    foreach ($whilelist as $pattern) {
        // var_dump($pattern);exit();
        if (preg_match($pattern, $path)) {
            $inWhiteList = 1;
            break;
        }
    }

    $authMethod = $request->headers->get('X-Auth-Method');

    if ($authMethod == 'key') {
        $accessKey = $request->headers->get('X-Auth-Key');
        $secretKey = $request->headers->get('X-Auth-Secret');

        if (empty($accessKey) or empty($secretKey)) {
            throw createAccessDeniedException("Auth Params is invalid.");
        }

        $settings = ServiceKernel::instance()->createService('System.SettingService')->get('storage', array());
        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            throw createAccessDeniedException("Auth Params is invalid..");
        }

        if (($accessKey != $settings['cloud_access_key']) || ($secretKey != $settings['cloud_secret_key'])) {
            throw createAccessDeniedException("Auth Params is invalid...");
        }

    } else {
        $token = $request->headers->get('X-Auth-Token');
        if (empty($token)) {
            // 兼容老的协议，即将去除
            $token = $request->headers->get('Auth-Token', '');
        }

        if (!$inWhiteList && empty($token)) {
            throw createNotFoundException("AuthToken is not exist.");
        }

        $userService = ServiceKernel::instance()->createService('User.UserService');
        $token = $userService->getToken('mobile_login', $token);

        if (!$inWhiteList && empty($token['userId'])) {
            throw createAccessDeniedException("AuthToken is invalid.");
        }

        $user = $userService->getUser($token['userId']);
        if (!$inWhiteList && empty($user)) {
            throw createNotFoundException("Auth user is not found.");
        }

        setCurrentUser($user);
    }

});

$app->error(function (\Exception $e, $code) {
    return array(
        'error' => array(
            'code' => $code,
            'message' => $e->getMessage(),
        ),
    );
});

include __DIR__ . '/config/routing.php';

$app->run();