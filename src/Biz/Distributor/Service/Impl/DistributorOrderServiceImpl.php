<?php

namespace Biz\Distributor\Service\Impl;

class DistributorOrderServiceImpl extends BaseDistributorServiceImpl
{
    public function getSendType($data)
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

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
