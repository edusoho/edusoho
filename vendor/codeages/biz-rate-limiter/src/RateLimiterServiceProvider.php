<?php
namespace Codeages\Biz\RateLimiter;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Codeages\RateLimiter\Storage\MySQLPDOStorage;
use Codeages\RateLimiter\Storage\RedisStorage;
use Codeages\RateLimiter\RateLimiter;

class RateLimiterServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['ratelimiter.factory'] = function($container) {
            return function($name, $maxAllowance, $period) use ($container) {
                return new RateLimiter($name, $maxAllowance, $period, $container['ratelimiter.storage']);
            };
        };

        $container['ratelimiter.storage'] = function($container) {
            return $container['ratelimiter.storage.'.$container['ratelimiter.storage_name']];
        };

        $container['ratelimiter.storage.mysql'] = function($container) {
            $pdo = $container['db']->getWrappedConnection();
            return new MySQLPDOStorage($pdo);
        };

        $container['ratelimiter.storage.redis'] = function($container) {
            return new RedisStorage();
        };

        $container['ratelimiter.storage_name'] = 'mysql';

        $container['migration.directories'][] = dirname(__DIR__) . '/migrations';
    }
}
