<?php

use Symfony\Component\Yaml\Yaml;

$parameters = Yaml::parse(file_get_contents(__DIR__ . '/../app/config/parameters.yml'));
$parameters['parameters']['topxia.upload.public_directory'] = __DIR__ .'/../web/files';
$parameters['parameters']['kernel.logs_dir'] = __DIR__ .'/../app/logs';

return $parameters['parameters'];