<?php

use Symfony\Component\Yaml\Yaml;

$environment = 'dev';
$parameters  = Yaml::parse(file_get_contents(__DIR__.'/../app/config/parameters.yml'));

if (file_exists(__DIR__.'/../app/config/parameters_service.yml')) {
    $serviceParameters        = Yaml::parse(file_get_contents(__DIR__.'/../app/config/parameters_service.yml'));
    $parameters['parameters'] = array_merge($parameters['parameters'], $serviceParameters['parameters']);
}

$parameters['parameters']['topxia.upload.public_directory'] = __DIR__.'/../web/files';
$parameters['parameters']['kernel.logs_dir']                = __DIR__.'/../app/logs';
$parameters['parameters']['environment']                    = $environment;
$parameters['parameters']['kernel.cache_dir']               = __DIR__.'/../app/cache/'.$environment;
return $parameters['parameters'];
