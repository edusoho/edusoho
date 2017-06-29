<?php

namespace Biz\Order\OrderRefundProcessor;

class OrderRefundProcessorFactory
{
    public static function create($target)
    {
        if (empty($target)) {
            throw new \Exception('订单类型不存在');
        }

        $class = __NAMESPACE__.'\\'.ucfirst($target).'OrderRefundProcessor';

        return new $class();
    }
}
