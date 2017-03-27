<?php

return array(
    'env' => 'test',
    'debug' => true,
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'biz_framework_test',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8',
    ),
    'redis.options' => array(
        'host' => '127.0.0.1',
        'port' => ''
    )
);
