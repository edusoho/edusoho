<?php

namespace Biz\S2B2C;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Topxia\Service\Common\ServiceKernel;

class S2B2CProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        // 日志
        $biz['s2b2c.merchant.logger'] = function () {
            $logger = new Logger('S2B2CMerchant');
            $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/s2b2c.log', Logger::DEBUG));

            return $logger;
        };

        // 接口
        /*
         * @param $biz
         * @return SupplierPlatformApi
         */
        $biz['supplier.platform_api'] = function ($biz) {
            return new SupplierPlatformApi($biz);
        };
    }
}
