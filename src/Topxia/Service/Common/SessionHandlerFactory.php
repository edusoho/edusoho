<?php
namespace Topxia\Service\Common;


use Symfony\Component\DependencyInjection\ContainerInterface;

class SessionHandlerFactory
{

    public static function getSessionHandler(ContainerInterface $container)
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
