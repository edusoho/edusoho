<?php

use Symfony\Component\HttpFoundation\ParameterBag;
use Topxia\Service\Common\ServiceKernel;

require __DIR__ . '/../vendor/autoload.php';

define("INSTALL_URI", "\/install\/start-install.php");

$serviceKernel = ServiceKernel::create('prod', true);
$serviceKernel->setParameterBag(new ParameterBag(array(
    'kernel.root_dir' => realpath(__DIR__ . '/../app')
)));


$parameters = file_get_contents(__DIR__ . "/../app/config/parameters.yml");
$parameters = \Symfony\Component\Yaml\Yaml::parse($parameters);
$parameters = $parameters['parameters'];

$biz = new \Codeages\Biz\Framework\Context\Biz(array(
    'debug'              => false,
    'db.options'         => array(
        'dbname'   => $parameters['database_name'],
        'user'     => $parameters['database_user'],
        'password' => $parameters['database_password'],
        'host'     => $parameters['database_host'],
        'port'     => $parameters['database_port'],
        'driver'   => $parameters['database_driver'],
        'charset'  => 'UTF8'
    ),
    'cache_directory'    => "%kernel.root_dir%/../var/cache",
    'tmp_directory'      => "%kernel.root_dir%/../var/tmp",
    'log_directory'      => "%kernel.root_dir%/../var/logs",
    'plugin.directory'   => "%kernel.root_dir%/../plugins",
    'plugin.config_file' => "%kernel.root_dir%/config/plugin_installed.php"
));

$biz['migration.directories'][] = dirname(__DIR__) . '/migrations';
$biz->register(new \Codeages\Biz\Framework\Provider\DoctrineServiceProvider());
$biz['subscribers'] = new \ArrayObject();
$biz->boot();

$serviceKernel->setBiz($biz);
