<?php

require_once __DIR__ . '/app/autoload.php';
require_once __DIR__ . '/app/bootstrap.php.cache';
require_once __DIR__ . '/app/AppKernel.php';


$env = getAppEvn($argv);

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$kernel = new AppKernel($env, true);
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
$biz->register(new \Codeages\Biz\Framework\Provider\MonologServiceProvider(), array('monolog.logfile' => $biz['log_directory'] . '/biz.log',));
$biz->register(new \Codeages\Biz\Order\OrderServiceProvider());
$biz->register(new \Codeages\Biz\Pay\PayServiceProvider());
$biz->boot();

return $biz;

/**
 * @param $arguments
 * @return mixed|string
 */
function getAppEvn($arguments)
{
    //check if set variable environment
    $variables = array_filter($arguments, function ($arg) {
        return strpos($arg, '-e=') === 0 || strpos($arg, '--env=') === 0;
    });

    if (empty($variables)) {
        return $env = 'dev';
    }
    //get first environment
    $env = array_shift($variables);
    // get environment value
    $variables = explode("=", $env);
    return $env = array_pop($variables);
}
