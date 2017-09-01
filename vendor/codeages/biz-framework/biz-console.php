<?php

use Codeages\Biz\Framework\Context\Biz;

use Codeages\Biz\Framework\Provider\DoctrineServiceProvider;
use Codeages\Biz\Framework\Provider\QueueServiceProvider;

$options = array(
    'db.options' => array(
        'dbname' => getenv('DB_NAME') ?: 'biz-framework',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: 3306,
        'driver' => 'pdo_mysql',
        'charset' => 'utf8',
    ),
    'redis.options' => array(
        'host' => getenv('REDIS_HOST'),
    ),
    'debug' => true,
);

$biz = new Biz($options);
$biz->register(new DoctrineServiceProvider());
$biz->register(new QueueServiceProvider());

$biz->boot();

return $biz;