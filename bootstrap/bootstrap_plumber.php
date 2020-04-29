<?php

use Pimple\Psr11\Container as PsrContainer;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\HttpFoundation\Request;

$loader = require __DIR__.'/../vendor/autoload.php';
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);

if (file_exists(__DIR__.'/../app/config/plumber.php')) {
    $options = include __DIR__.'/../app/config/plumber.php';
}

$input = new ArgvInput();
$env = $input->getParameterOption(['--env', '-e'], getenv('SYMFONY_ENV') ?: 'dev');

$request = Request::createFromGlobals();

$kernel = new AppKernel($env, true);
$kernel->setRequest($request);
$kernel->boot();

$container = $kernel->getContainer();

$biz = $container->get('biz');
$biz['biz'] = $biz;

$plumberQueueDatabases = $container->getParameter('plumber_queue_databases');

foreach ($options['queues'] as $key => &$queue) {
    if (!empty($plumberQueueDatabases[$key])) {
        $queue = array_merge($queue, $plumberQueueDatabases[$key]);
    }
}

$options = array_merge($options, [
    'log_path' => $container->getParameter('kernel.logs_dir').'/plumber.log',
    'pid_path' => $container->getParameter('kernel.root_dir').'/data/plumber.pid',
]);

return [
    'options' => $options,
    'container' => new PsrContainer($biz),
];
