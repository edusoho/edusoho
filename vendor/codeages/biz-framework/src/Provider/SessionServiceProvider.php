<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Session\Storage\DbSessionStorage;
use Codeages\Biz\Framework\Session\Storage\RedisSessionStorage;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SessionServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['autoload.aliases']['Session'] = 'Codeages\Biz\Framework\Session';


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
