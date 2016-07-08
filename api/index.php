<?php

date_default_timezone_set('UTC');

require_once __DIR__.'/../vendor2/autoload.php';

use Doctrine\DBAL\DriverManager;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Api\ApiAuth;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;

if (API_ENV == 'prod') {
    ErrorHandler::register(0);
    ExceptionHandler::register(false);
}

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
$serviceKernel->getConnection()->exec('SET NAMES UTF8');

include __DIR__.'/src/functions.php';

$app = new Silex\Application();

include __DIR__ . '/config/' . API_ENV . '.php';

$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/templates',
));

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

$app->error(function (\Exception $exception, $code) use ($app) {
    $error = array(
        'code' => $code,
        'message' => $exception->getMessage(),
    );

    if ($app['debug']) {

        if (!$exception instanceof FlattenException) {
            $exception = FlattenException::create($exception);
        }

        $error['previous'] = array();

        $flags = PHP_VERSION_ID >= 50400 ? ENT_QUOTES | ENT_SUBSTITUTE : ENT_QUOTES;

        $count = count($exception->getAllPrevious());
        $total = $count + 1;
        foreach ($exception->toArray() as $position => $e) {
            $previous = array();

            $ind = $count - $position + 1;

            $previous['message'] = "{$ind}/{$total} {$e['class']}: {$e['message']}";
            $previous['trace'] = array();

            foreach ($e['trace'] as $position => $trace) {
                $content = sprintf('%s. ', $position+1);
                if ($trace['function']) {
                    // var_dump($trace['args']);
                    $content .= sprintf('at %s%s%s(%s)', $trace['class'], $trace['type'], $trace['function'], '...args...');
                }
                if (isset($trace['file']) && isset($trace['line'])) {
                    $content .= sprintf(' in %s line %d', htmlspecialchars($trace['file'], $flags, 'UTF-8'), $trace['line']);
                }

                $previous['trace'][] = $content;
            }

            $error['previous'][] = $previous;
        }

    }

    return $error;

});

$app->run();
