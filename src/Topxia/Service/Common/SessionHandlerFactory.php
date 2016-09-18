<?php
namespace Topxia\Service\Common;

class SessionHandlerFactory
{
    private static $container;
    private static $instance;

    public static function getSessionHandler($container)
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

    private static function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}
