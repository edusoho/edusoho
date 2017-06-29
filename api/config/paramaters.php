<?php

use Symfony\Component\Yaml\Yaml;

$parameters = Yaml::parse(file_get_contents(__DIR__.'/../../app/config/parameters.yml'));

if (file_exists(__DIR__.'/../../app/config/parameters_service.yml')) {
    $serviceParameters        = Yaml::parse(file_get_contents(__DIR__.'/../../app/config/parameters_service.yml'));
    $parameters['parameters'] = array_merge($parameters['parameters'], $serviceParameters['parameters']);
}

$parameters['parameters']['topxia.upload.public_directory'] = __DIR__.'/../../web/files';
$parameters['parameters']['kernel.logs_dir']                = __DIR__.'/../../app/logs';
$parameters['parameters']['environment']                    = API_ENV;
$parameters['parameters']['kernel.cache_dir']               = __DIR__.'/../../app/cache/'.API_ENV;
$parameters['parameters']['kernel.root_dir']               = __DIR__.'/../../app/';
return $parameters['parameters'];
