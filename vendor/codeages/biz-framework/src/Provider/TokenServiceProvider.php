<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TokenServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['token_service.impl'] = isset($container['token_service.impl']) ? $container['token_service.impl'] : 'database';
        $container['token_service.gc_divisor'] = isset($container['token_service.gc_divisor']) ? intval($container['token_service.gc_divisor']) : 1000;

        if ($container['token_service.impl'] == 'database') {
            $container['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/token';
        }

        $container['@Token:TokenService'] = function ($container) {
            $class = 'Codeages\\Biz\\Framework\\Token\\Service\\Impl\\'.ucfirst($container['token_service.impl']).'TokenServiceImpl';

            return new $class($container);
        };

        $container['autoload.aliases']['Token'] = 'Codeages\\Biz\\Framework\\Token';
    }
}
