<?php

use Symfony\Component\Yaml\Yaml;

$config = Yaml::parse(file_get_contents(__DIR__ . '/../app/config/parameters.yml'));

return $config['parameters'];