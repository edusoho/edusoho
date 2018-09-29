<?php

use Codeages\Biz\Framework\Context\Biz;

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
    'log_dir' => __DIR__.'/var/logs',
    'run_dir' => __DIR__.'/var/run',
    'lock.flock.directory' => __DIR__.'/var/run',
);

$biz = new Biz($options);
$biz->register(new \Codeages\Biz\Framework\Provider\DoctrineServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\QueueServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\TokenServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\SchedulerServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\SettingServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\TargetlogServiceProvider());
$biz->register(new \Codeages\Biz\Framework\Provider\MonologServiceProvider(), [
    'monolog.logfile' => $biz['log_dir'].'/biz.log',
]);
$biz->register(new \Codeages\Biz\Framework\Provider\SessionServiceProvider());

$biz['queue.connection.default'] = function ($biz) {
    return new \Codeages\Biz\Framework\Queue\Driver\DatabaseQueue('default', $biz);
};

$biz->boot();

return $biz;
