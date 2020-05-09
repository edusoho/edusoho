<?php

namespace Biz\Plumber;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Topxia\Service\Common\ServiceKernel;

class PlumberProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['plumber.queue.logger'] = function () {
            $logger = new Logger('Plumber');
            $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/plumber_queue.log', Logger::DEBUG));

            return $logger;
        };

        $biz['plumber.logger'] = function () {
            $logger = new Logger('Plumber');
            $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/plumber.log', Logger::DEBUG));

            return $logger;
        };
    }
}
