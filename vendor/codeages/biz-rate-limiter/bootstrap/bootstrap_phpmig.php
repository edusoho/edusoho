<?php
use Codeages\Biz\Framework\Dao\MigrationBootstrap;

use Codeages\Biz\Framework\Context\Biz;

$config = require dirname(__DIR__) . '/config/biz.php';

$biz = new Biz($config);
$biz['migration.directories'][] = dirname(__DIR__) . '/migrations';
$biz->register(new \Codeages\Biz\Framework\Provider\DoctrineServiceProvider());
$biz->boot();

$migration = new MigrationBootstrap($biz['db'], $biz['migration.directories']);

return $migration->boot();