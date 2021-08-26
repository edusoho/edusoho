<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Session\Storage\DbSessionStorage;
use Codeages\Biz\Framework\Session\Storage\RedisSessionStorage;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Redis;

class SessionServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['autoload.aliases']['Session'] = 'Codeages\Biz\Framework\Session';

        $container['session.options'] = isset($container['session.options']) ? $container['session.options'] : array(
            'max_life_time' => 7200,
            'session_storage' => 'db', // exapmle: db, redis
        );

        # session redis
        $container['session.redis'] = function () use ($container) {
            if ($container['session.options']['session_storage'] != 'redis') {
                return null;
            }

            $options = $container['session.options'];
            $redis = new Redis();
            $redis->connect($options['session_redis_host'], $options['session_redis_port'], $options['session_redis_timeout'], $options['session_redis_reserved'], $options['session_redis_retry_interval']);
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            if ($options['session_redis_key_prefix']) {
                $redis->setOption(Redis::OPT_PREFIX, $options['session_redis_key_prefix']);
            }
            if (!empty($options['session_redis_password'])) {
                $redis->auth($options['session_redis_password']);
            }

            return $redis;
        };

        $container['session.storage.db'] = function () use ($container) {
            return new DbSessionStorage($container);
        };

        $container['session.storage.redis'] = function () use ($container) {
            return new RedisSessionStorage($container);
        };

        $container['console.commands'][] = function () use ($container) {
            return new \Codeages\Biz\Framework\Session\Command\TableCommand($container);
        };
    }
}
