<?php

namespace Biz\Common;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SessionHandlerFactory
{
    public static function getSessionHandler($biz, ContainerInterface $container)
    {
        $redisPath = $container->getParameter('kernel.root_dir').'/data/redis.php';
        if (file_exists($redisPath) && !empty($biz['cache.cluster'])) {
            return $container->get('session.handler.redis');
        }

        return $container->get('session.handler.pdo');
    }
}
