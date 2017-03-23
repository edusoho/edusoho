<?php

/*
 * 此文件来自 Silex 项目(https://github.com/silexphp/Silex).
 *
 * 版权信息请看 LICENSE.SILEX
 */

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\Common\EventManager;
use Symfony\Bridge\Doctrine\Logger\DbalLogger;
use Codeages\Biz\Framework\Redis\RedisCluster;
use Codeages\Biz\Framework\Dao\DaoProxy\CacheDaoProxy;
use Codeages\Biz\Framework\Dao\DaoProxy\CacheDelegate;
use Codeages\Biz\Framework\Dao\CacheStrategy\TableCacheStrategy;
use Codeages\Biz\Framework\Dao\CacheStrategy\PromiseCacheStrategy;

/**
 * Cache Provider.
 */
class CacheServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['cache.config'] = array(
            'maxLifeTime' => 86400,
            'default' => array(
                "host"           => "127.0.0.1",
                "port"           => 6379,
                "timeout"        => 1,
                "reserved"       => null,
                "retry_interval" => 100
            )
        );

        $app['cache.cluster'] = $app->factory(function($app) {
            return new RedisCluster($app);
        });

        $app['dao.proxy'] = $app->factory(function($app) {
            return new CacheDaoProxy($app);
        });

        $app['cache.dao.delegate'] = function ($app) {
            return new CacheDelegate($app);
        };

        $app['cache.dao.strategy.table'] = function ($app) {
            return new TableCacheStrategy($app);
        };

        $app['cache.dao.strategy.promise'] = function ($app) {
            return new PromiseCacheStrategy($app);
        };
    }
}
