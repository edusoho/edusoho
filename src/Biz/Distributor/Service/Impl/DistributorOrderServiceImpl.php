<?php

namespace Biz\Distributor\Service\Impl;

use QiQiuYun\SDK\Auth;
use Biz\Distributor\Util\DistributorJobStatus;

class DistributorOrderServiceImpl extends BaseDistributorServiceImpl
{
    public function getSendType()
    {
        return 'order';
    }

    public function getNextJobType()
    {
        return 'User';
    }

    protected function convertData($order)
    {
        $item = $this->getOrderService()->findOrderItemsByOrderId($order['id']);

        return array(
            'user_source_id' => $order['user_id'],
            'source_id' => $order['id'],
            'product_type' => $item[0]['target_type'],
            'product_id' => $item[0]['target_id'],
            'title' => $order['title'],
            'sn' => $order['sn'],
            'order_created_time' => $order['created_time'],
            'payment_time' => $order['pay_time'],
            'refund_expiry_day' => $order['expired_refund_days'],
            'refund_deadline' => $order['refund_deadline'],
            'price' => $order['price_amount'],
            'pay_amount' => $order['pay_amount'],
            'deduction' => $order['price_amount'] - $order['pay_amount'],
            'status' => $order['status'],
        );
    }

    protected function getJobType()
    {
        return 'Order';
    }

    protected function getDependentTarget($order)
    {
        $userJobData = $this->getDistributorJobDataDao()->search(
            array('status' => DistributorJobStatus::$FINISHED, 'target' => 'user:'.$order['user_id']),
            array('id' => 'DESC'),
            0,
            1
        );

        if (empty($userJobData)) {
            return '';
        } else {
            return 'user:'.$order['userId'];
        }
    }

    private function sign($arr, $time, $once)
    {
        ksort($arr);
        $json = implode('\n', array($time, $once, json_encode($arr)));
        $settings = $this->getSettingService()->get('storage', array());
        $auth = new Auth($settings['cloud_access_key'], $settings['cloud_secret_key']);

        return $auth->sign($json);
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
