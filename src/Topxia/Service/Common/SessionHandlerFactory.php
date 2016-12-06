<?php
namespace Topxia\Service\Common;


use Symfony\Component\DependencyInjection\ContainerInterface;

class SessionHandlerFactory
{
    public static function getSessionHandler(ContainerInterface $container)
    {
        $redisPath = $container->getParameter('kernel.root_dir').'/data/redis.php';
        if (file_exists($redisPath)) {
            $redisFactory = $container->get('session.handler.redis.factory');

            if ($redisFactory->getRedis()) {
                return $container->get('session.handler.redis');
            }
        }

        return $container->get('session.handler.pdo');
    }
}
