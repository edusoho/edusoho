<?php

namespace Tests;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Dao\MigrationBootstrap;
use Codeages\Biz\Framework\Provider\DoctrineServiceProvider;
use PHPUnit\Framework\TestCase;

class MigrationBootstrapTest extends TestCase
{
    public function testBoot()
    {
        $config = array(
            'db.options' => array(
                'driver' => 'pdo_mysql',
                'dbname' => getenv('DB_NAME'),
                'host' => getenv('DB_HOST'),
                'user' => getenv('DB_USER'),
                'password' => getenv('DB_PASSWORD'),
                'charset' => 'utf8',
                'port' => getenv('DB_PORT'),
            ),
        );
        $biz = new Biz($config);
        $biz['migration.directories'][] = dirname(__DIR__).'/Example/migrations';
        $biz->register(new DoctrineServiceProvider());
        $biz->boot();

        $bootstrap = new MigrationBootstrap($biz['db'], $biz['migration.directories'], 'new-migrations');
        $container = $bootstrap->boot();

        $this->assertInstanceOf('Pimple\Container', $container);
        $this->assertInstanceOf('Phpmig\Adapter\AdapterInterface', $container['phpmig.adapter']);
    }
}
