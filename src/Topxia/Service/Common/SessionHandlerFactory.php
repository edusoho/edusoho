<?php
namespace Topxia\Service\Common;

class SessionHandlerFactory
{
    private static $container;
    private static $instance;

    public static function getSessionHandler($container)
    {
        $sessionHandler = self::getSettingService()->get('sessionHandler');

        if (isset($sessionHandler['mode']) && $sessionHandler['mode'] == 'redis') {
            $redisFactory = $container->get('session.handler.redis.factory');

            if ($redisFactory->getRedis()) {
                return $container->get('session.handler.redis');
            }
        } elseif (isset($sessionHandler['mode']) && $sessionHandler['mode'] == 'native') {
            return $container->get('session.handler.native_file');
        }

        return $container->get('session.handler.pdo');
    }

    private static function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}
