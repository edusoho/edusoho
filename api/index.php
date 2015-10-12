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
$config = include __DIR__ . '/config.php';
$config['host'] = 'http://'.$_SERVER['HTTP_HOST'];

$connection = DriverManager::getConnection(array(
    'dbname' => $config['database_name'],
    'user' => $config['database_user'],
    'password' => $config['database_password'],   
    'host' => $config['database_host'],
    'driver' => $config['database_driver'],
    'charset' => 'utf8',
));


$serviceKernel = ServiceKernel::create($config['environment'], true);
$serviceKernel->setParameterBag(new ParameterBag($config));
$serviceKernel->setConnection($connection);
// $serviceKernel->getConnection()->exec('SET NAMES UTF8');


include __DIR__ . '/src/functions.php';


$app = new Silex\Application();
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app['debug'] = true;

$app->view(function (array $result, Request $request) use ($app) {
    return new JsonResponse($result);
});

include __DIR__ . '/config/container.php';


$app->before(function (Request $request) use ($app) {

    $whiteLists = include __DIR__ . '/whiteList.php';
    $whiteLists = $request->getMethod() == 'GET' ? $whiteLists['get'] : $whiteLists['post'];
    $inWhiteList = 0;
    foreach ($whiteLists as $whiteList) {
        $path = $request->getPathInfo();
        if (preg_match($whiteList, $request->getPathInfo())) {
            $inWhiteList = 1;
            break;
        }
    }
    $token = $request->headers->get('Auth-Token', '');
    if (!$inWhiteList && empty($token)) {
        throw createNotFoundException("AuthToken is not exist.");
    }
    $userService = ServiceKernel::instance()->createService('User.UserService');
    $token = $userService->getToken('mobile_login', $token);

    if (!$inWhiteList && empty($token['userId'])) {
        throw createAccessDeniedException("AuthToken is invalid.");
    }

    $user = $userService->getUser($token['userId']);
    // $user = $userService->getUser(1);
    if (!$inWhiteList && empty($user)) {
        throw createNotFoundException("Auth user is not found.");
    }
    setCurrentUser($user);

});

$app->error(function (\Exception $e, $code) {
    return array(
        'code' => $code,
        'message' => $e->getMessage()
    );
});

include __DIR__ . '/config/routing.php';

$app->run();