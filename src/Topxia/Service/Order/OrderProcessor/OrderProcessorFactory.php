<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Order\OrderProcessor\OrderProcessor;

class OrderProcessorFactory
{
    public static function create($target)
    {
        if (empty($target)) {
            throw new \Exception(self::getKernel()->trans('订单类型不存在'));
        }

        $class = __NAMESPACE__.'\\'.ucfirst($target).'OrderProcessor';

        return new $class();
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
