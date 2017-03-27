<?php

date_default_timezone_set('UTC');

require_once __DIR__ . '/bootstrap.php';

use Topxia\Api\ApiAuth;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

include __DIR__.'/src/functions.php';

$app = new Silex\Application();

include __DIR__ . '/config/' . API_ENV . '.php';

$app->register(new Silex\Provider\ServiceControllerServiceProvider());
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
