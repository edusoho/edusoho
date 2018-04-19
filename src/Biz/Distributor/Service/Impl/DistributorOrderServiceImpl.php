<?php

namespace Biz\Distributor\Service\Impl;

use Biz\Distributor\Util\DistributorUtil;
use AppBundle\Common\Exception\RuntimeException;

class DistributorOrderServiceImpl extends BaseDistributorServiceImpl
{
    public function getSendType()
    {
        return 'order';
    }

    protected function getJobType()
    {
        return 'Order';
    }

    protected function convertData($order)
    {
        $item = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
        $orderDeducts = $this->getOrderService()->findOrderItemDeductsByOrderId($order['id']);
        $deduct = array();
        foreach ($orderDeducts as $orderDeduct) {
            $deduct[] = array(
                'type' => $orderDeduct['deduct_type'],
                'detail' => $orderDeduct['deduct_type_name'],
                'amount' => $orderDeduct['deduct_amount'],
            );
        }

        return array(
            'user_source_id' => $order['user_id'],
            'source_id' => $order['id'],
            'product_type' => $item[0]['target_type'],
            'product_id' => $item[0]['target_id'],
            'title' => $order['title'],
            'sn' => $order['sn'],
            'created_time' => $order['created_time'],
            'payment_time' => $order['pay_time'],
            'refund_expiry_day' => $order['expired_refund_days'],
            'refund_deadline' => $order['refund_deadline'],
            'price' => $order['price_amount'],
            'pay_amount' => $order['pay_amount'],
            'deduction' => $deduct,
            'status' => $order['status'],
            'updated_time' => $order['updated_time'],
        );
    }

    public function getRoutingInfo($token)
    {
        $type = DistributorUtil::getType($token);
        if (!in_array($type, array('course'))) {
            throw new RuntimeException('token error');
        }

        $class = 'Distributor'.ucfirst($type).'OrderService';
        $distributorProduct = $this->createService("Distributor:{$class}");
        $tokenInfo = $distributorProduct->decodeToken($token);

        return array($distributorProduct->getRoutingName(), $distributorProduct->getRoutingParams($tokenInfo));
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
