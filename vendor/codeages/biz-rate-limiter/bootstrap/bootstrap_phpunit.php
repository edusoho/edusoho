<?php
use Codeages\Biz\Framework\Dao\MigrationBootstrap;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\UnitTests\UnitTestsBootstrap;

require dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/biz.php';

$biz = new Biz($config);
$biz['migration.directories'][] = dirname(__DIR__) . '/migrations';
$biz->register(new \Codeages\Biz\Framework\Provider\DoctrineServiceProvider());
$biz->boot();

$bootstrap = new UnitTestsBootstrap($biz);
$bootstrap->boot();