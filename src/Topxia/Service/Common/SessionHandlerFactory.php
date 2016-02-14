<?php
namespace Topxia\Service\Common;

class SessionHandlerFactory
{
    private static $container;
    private static $instance;

    public static function getSessionHandler($container)
    {
        $redisSetting = self::getSettingService()->get('redis');

        if (isset($redisSetting['opened']) && $redisSetting['opened']) {
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
