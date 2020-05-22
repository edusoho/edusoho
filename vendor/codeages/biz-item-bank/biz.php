<?php

use Codeages\Biz\Framework\Context\Biz;

$options = array(
    'db.options' => array(
        'dbname' => getenv('DB_NAME') ?: 'biz_item_bank',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: 3306,
        'driver' => 'pdo_mysql',
        'charset' => 'utf8',
    ),
    'debug' => true,
    'cache_dir' => __DIR__ . '/var/cache',
    'log_dir' => __DIR__ . '/var/logs',
    'run_dir' => __DIR__ . '/var/run',
    'lock.flock.directory' => __DIR__ . '/var/run',
);

$biz = new Biz($options);
$biz->register(new \Codeages\Biz\Framework\Provider\DoctrineServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\TargetlogServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\MonologServiceProvider(), [
    'monolog.logfile' => $biz['log_dir'].'/biz.log',
]);
$biz->register(new \Codeages\Biz\ItemBank\ItemBankServiceProvider());

$biz->boot();

return $biz;
