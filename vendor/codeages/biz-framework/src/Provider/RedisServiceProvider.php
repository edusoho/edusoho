<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Redis;
use RedisArray;

class RedisServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['redis.default_options'] = array(
            'host' => '127.0.0.1:6379',
            'password' => '',
            'timeout' => 1,
            'reserved' => null,
            'retry_interval' => 100,
            'key_prefix' => '',
        );

        $app['mult_redis.options.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($app['mult_redis.options'])) {
                $app['mult_redis.options'] = array('default' => isset($app['redis.options']) ? $app['redis.options'] : array());
            }

            $tmp = $app['mult_redis.options'];
            foreach ($tmp as $name => &$options) {
                $options = array_replace($app['redis.default_options'], $options);

                if (!isset($app['mult_redis.default'])) {
                    $app['mult_redis.default'] = $name;
                }
            }

            $app['mult_redis.options'] = $tmp;
        });

        $app['mult_redis'] = function ($app) {
            $app['mult_redis.options.initializer']();

            $multRedis = new Container();
            foreach ($app['mult_redis.options'] as $name => $options) {
                $multRedis[$name] = function () use ($options) {
                    $hosts = explode(',', $options['host']);

                    if (1 == count($hosts)) {
                        list($host, $port) = explode(':', $hosts[0]);
                        $redis = new Redis();

                        if (!empty($options['pconnect'])) {
                            $redis->pconnect($host, $port, $options['timeout']);
                        } else {
                            $redis->connect($host, $port, $options['timeout'], $options['reserved'], $options['retry_interval']);
                        }
                    } else {
                        $redis = new RedisArray($hosts);
                    }

                    $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
                    if ($options['key_prefix']) {
                        $redis->setOption(Redis::OPT_PREFIX, $options['key_prefix']);
                    }
                    if (!empty($options['password'])) {
                        $redis->auth($options['password']);
                    }

                    return $redis;
                };
            }

            return $multRedis;
        };

        $this->registerShortcutForFirstDb($app);
    }

    private function registerShortcutForFirstDb($app)
    {
        $app['redis'] = function ($app) {
            $dbs = $app['mult_redis'];

            return $dbs[$app['mult_redis.default']];
        };
    }
}
