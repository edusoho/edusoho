<?php

use Pimple\Psr11\Container as PsrContainer;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\HttpFoundation\Request;

$loader = require __DIR__.'/../vendor/autoload.php';
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);

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

$options = [
    'app_name' => 's2b2c_m_plumber',
    'queues' => [
        'example_queue' => array_merge([
            'type' => 'beanstalk',
            'enable_queue' => true,
            'host' => '127.0.0.1',
            'port' => 11300,
            'password' => '',
        ], $plumberQueueDatabases['example_queue']),
    ],
    'workers' => [
        'worker_1' => [
            'topic' => 'merchant.example',
            'num' => 1,
            'class' => 'AppBundle\Worker\ExampleWorker',
            'queue' => 'example_queue',
        ],
    ],
    'log_path' => $container->getParameter('kernel.logs_dir').'/plumber.log',
    'pid_path' => $container->getParameter('kernel.root_dir').'/data/plumber.pid',
];

return [
    'options' => $options,
    'container' => new PsrContainer($biz),
];
