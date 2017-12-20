<?php

use Symfony\Component\HttpFoundation\ParameterBag;
use Topxia\Service\Common\ServiceKernel;

require_once __DIR__.'/../vendor/autoload.php';

define('INSTALL_URI', "\/install\/start-install.php");
define('ROOT_DIR', realpath(__DIR__.'/../app'));

$serviceKernel = ServiceKernel::create('prod', true);
$serviceKernel->setParameterBag(new ParameterBag(array(
    'kernel.root_dir' => ROOT_DIR,
)));

$parameters = file_get_contents(__DIR__.'/../app/config/parameters.yml');
$parameters = \Symfony\Component\Yaml\Yaml::parse($parameters);
$parameters = $parameters['parameters'];

$biz = new \Codeages\Biz\Framework\Context\Biz(array(
    'debug' => false,
    'db.options' => array(
        'dbname' => $parameters['database_name'],
        'user' => $parameters['database_user'],
        'password' => $parameters['database_password'],
        'host' => $parameters['database_host'],
        'port' => $parameters['database_port'],
        'driver' => $parameters['database_driver'],
        'charset' => 'UTF8',
    ),
    'cache_directory' => ROOT_DIR.'/cache',
    'tmp_directory' => ROOT_DIR.'/tmp',
    'log_directory' => ROOT_DIR.'/logs',
    'plugin.directory' => ROOT_DIR.'/../plugins',
    'plugin.config_file' => ROOT_DIR.'/config/plugin_installed.php',
));

$biz['migration.directories'][] = dirname(__DIR__).'/migrations';
$biz->register(new \Codeages\Biz\Framework\Provider\DoctrineServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\SchedulerServiceProvider());
$biz->register(new \Codeages\Biz\Pay\PayServiceProvider());
$biz['subscribers'] = new \ArrayObject();
$biz->boot();

$serviceKernel->setBiz($biz);
