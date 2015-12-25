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
            return $container->get('session.handler.redis');
        } elseif (isset($sessionHandler['mode']) && $sessionHandler['mode'] == 'native') {
            return $container->get('session.handler.native_file');
        } else {
            return $container->get('session.handler.pdo');
        }

        return self::$instance;
    }

    private static function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}
