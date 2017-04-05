<?php

/*
 * 此文件来自 Silex 项目(https://github.com/silexphp/Silex).
 *
 * 版权信息请看 LICENSE.SILEX
 */

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Framework\Context\BizException;
use Redis;
use RedisArray;

class RedisServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['redis.default_options'] = array(
            'host' => '127.0.0.1:6379',
            'timeout' => 1,
            'reserved' => null,
            'retry_interval' => 100,
        );

        $container['redis'] = function ($container) {
            $options = array_replace($container['redis.default_options'], $container['redis.options']);
            if (!is_array($options['host'])) {
                $options['host'] = array((string) $options['host']);
            }

            if (empty($options['host'])) {
                throw new BizException("Biz value `cache.options`['host'] is error.");
            }

            if (count($options['host']) == 1) {
                list($host, $port) = explode(':', current($options['host']));
                $redis = new Redis();
                $redis->pconnect($host, $port, $options['timeout'], $options['reserved'], $options['retry_interval']);
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            } else {
                $redis = new RedisArray($options['host']);
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            }

            return $redis;
        };
    }
}
