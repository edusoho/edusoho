<?php

namespace Biz\Common;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SessionHandlerFactory
{
    public static function getSessionHandler($biz, ContainerInterface $container)
    {
        if ($container->hasParameter('redis_host')) {
            return $container->get('session.handler.redis');
        }

        return $container->get('session.handler.pdo');
    }
}
