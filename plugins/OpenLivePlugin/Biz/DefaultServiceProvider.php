<?php

namespace OpenLivePlugin\Biz;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenLivePlugin\Biz\OpenLivePlatform\PlatformSdk;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Topxia\Service\Common\ServiceKernel;

class DefaultServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        // 日志
        $biz['open_live.plugin.logger'] = function () {
            $logger = new Logger('OpenLivePlugin');
            $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/open-live-plugin.log', Logger::DEBUG));
            return $logger;
        };

        // open-live 接口
        $biz['open_live.plugin.open_live_platform'] = function ($biz) {
            return new PlatformSdk($biz);
        };
    }
}
