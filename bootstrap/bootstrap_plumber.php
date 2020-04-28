<?php

use Symfony\Component\HttpFoundation\Request;
use Pimple\Psr11\Container as PsrContainer;

if (file_exists(__DIR__.'/../web/merchant.php')) {
    include __DIR__.'/../web/merchant.php';
}

$kernel = new AppKernel('prod', true);
$kernel->setRequest(Request::createFromGlobals());
$kernel->boot();

$container = $kernel->getContainer();
$plumberConfig = $container->getParameter('plumber');
$queueConfig = $plumberConfig['queue'];
$workerConfig = $plumberConfig['worker'];

$options = array(
    'app_name' => $plumberConfig['name'],
    'queues' => array(
        'init_queue' => array(
            'type' => 'beanstalk',
            'host' => $queueConfig['init_queue']['beanstalk_host'],
            'port' => $queueConfig['init_queue']['beanstalk_port'],
            'password' => $queueConfig['init_queue']['beanstalk_password'],
        ),
        'default_queue' => array(
            'type' => 'beanstalk',
            'host' => $queueConfig['default_queue']['beanstalk_host'],
            'port' => $queueConfig['default_queue']['beanstalk_port'],
            'password' => $queueConfig['default_queue']['beanstalk_password'],
        ),
    ),
    'workers' => array(
        array(
            'class' => 'AppBundle\Worker\InitMerchantWorker',
            'num' => $workerConfig['init_worker']['worker_num'],
            'queue' => 'init_queue',
            'topic' => $workerConfig['init_worker']['topic_name'],
        ),
        array(
            'class' => 'AppBundle\Worker\CrontabJobWorker',
            'num' => $workerConfig['crontab_job_worker']['worker_num'],
            'queue' => 'default_queue',
            'topic' => $workerConfig['crontab_job_worker']['topic_name'],
        ),
    ),
    'log_path' => $container->getParameter('kernel.logs_dir').'/plumber.log',
    'pid_path' => $container->getParameter('kernel.root_dir').'/data/plumber.pid',
);

$biz = $container->get('biz');
$biz['biz'] = $biz;
$psrContainer = new PsrContainer($biz);

return array(
    'options' => $options,
    'container' => $psrContainer,
);
