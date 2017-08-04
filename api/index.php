<?php

date_default_timezone_set('UTC');

require_once __DIR__ . '/bootstrap.php';

use Symfony\Component\HttpFoundation\Response;
use Topxia\Api\ApiAuth;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;

include __DIR__.'/src/functions.php';

$app = new Silex\Application();

$app['biz'] = function (){
    global $kernel;
    return $kernel->getContainer()->get('biz');
};

$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/templates',
));

include __DIR__ . '/config/' . API_ENV . '.php';

$app->before(function (Request $request) use ($app) {
    $auth = new ApiAuth(include __DIR__ . '/config/whitelist.php');
    $auth->auth($request);
});

$app->error(function (\Exception $exception, Request $request, $code) use ($app) {
    $error = array(
        'code' => $code,
        'message' => $exception->getCode() > 0 ? $exception->getMessage() : '服务器内部错误',
    );

    if ($app['debug']) {
        $error['message'] = $exception->getMessage();
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

    return $app->json($error);
}, 100);

$app->view(function (array $result, Request $request) use ($app) {
    return $app->json($result);
});

$app->after(function (Request $request, Response $response) {
    global $kernel;
    if ($kernel->getContainer()->has('reward_point.response_decorator')) {
        $kernel->getContainer()->get('reward_point.response_decorator')->decorate($response);
    }

});

$app->run();
