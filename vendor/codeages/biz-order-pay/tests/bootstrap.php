<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Tests\IntegrationTestCase;

define('ROOT_DIR', dirname(__DIR__));

date_default_timezone_set('Asia/Shanghai');
$loader = require ROOT_DIR.'/vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
IntegrationTestCase::$classLoader = $loader;

// putenv("DB_NAME=biz-order-pay-test");
// putenv("DB_HOST=127.0.0.1");
// putenv("DB_USER=root");
// putenv("DB_PASSWORD=root");

$dns = sprintf('mysql:dbname=%s;host=%s', getenv('DB_NAME'), getenv('DB_HOST'));
$pdo = new PDO($dns, getenv('DB_USER'), getenv('DB_PASSWORD'));

echo "[exec] vendor/bin/phpmig migrate\n";
chdir(dirname(__DIR__));
passthru('vendor/bin/phpmig migrate');

unset($pdo);
