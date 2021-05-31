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

        $container['session.redis.options'] = isset($container['session.redis.options']) ? $container['session.redis.options'] : [];

        # session redis
        $container['session.redis'] = function () use ($container) {
            if (empty($container['session.redis.options'])) {
                return null;
            }

            $options = $container['session.redis.options'];

            $redis = new Redis();
            $redis->connect($options['host'], $options['port'], $options['timeout'], $options['reserved'], $options['retry_interval']);
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            if ($options['key_prefix']) {
                $redis->setOption(Redis::OPT_PREFIX, $options['key_prefix']);
            }
            if (!empty($options['password'])) {
                $redis->auth($options['password']);
            }

            return $redis;
        };

        $container['session.options'] = isset($container['session.options']) ? $container['session.options'] : array(
            'max_life_time' => 7200,
            'session_storage' => 'db', // exapmle: db, redis
        );

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
