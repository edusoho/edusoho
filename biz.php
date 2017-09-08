<?php

require_once __DIR__.'/app/autoload.php';
require_once __DIR__.'/app/bootstrap.php.cache';
require_once __DIR__.'/app/AppKernel.php';


$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$kernel = new AppKernel('dev', true);
$kernel->setRequest($request);
$kernel->boot();

$options = $kernel->getContainer()->getParameter('biz_config');


$biz = new Codeages\Biz\Framework\Context\Biz($options);
$biz->register(new \Codeages\Biz\Framework\Provider\DoctrineServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\QueueServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\TokenServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\SchedulerServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\SettingServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\TargetlogServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\MonologServiceProvider(), array(  'monolog.logfile' => $biz['log_directory'].'/biz.log',));
$biz->boot();

return $biz;