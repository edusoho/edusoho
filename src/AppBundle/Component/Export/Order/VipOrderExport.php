<?php

namespace AppBundle\Component\Export\Order;

class VipOrderExport extends OrderExport
{
    protected $target = 'vip';

    public function getTitles()
    {
        return array('订单号', '订单状态', '订单名称', '购买者', '姓名', '实付价格', '支付方式', '创建时间', '付款时间');
    }

    protected function handlerOrder($orders, $users, $profiles)
    {
        $ordersContent = array();

        $payment = $this->container->get('codeages_plugin.dict_twig_extension')->getDict('payment');
        $status = array(
            'created' => '未付款',
            'paid' => '已付款',
            'refunding' => '退款中',
            'refunded' => '已退款',
            'cancelled' => '已关闭',
        );

        foreach ($orders as $key => $order) {
            $member = array();
            $member[] = $order['sn'];
            $member[] = $status[$order['status']].',';
            $member[] = $order['title'];
            $member[] = $users[$order['userId']]['nickname'];
            $member[] = $profiles[$order['userId']]['truename'] ? $profiles[$order['userId']]['truename'].',' : '-'.',';
            $member[] = $order['amount'];
            $member[] = $payment[$order['payment']];
            $member[] = date('Y-n-d H:i:s', $order['createdTime']);

            if ($order['paidTime'] != 0) {
                $member[] = date('Y-n-d H:i:s', $order['paidTime']);
            } else {
                $member[] = '-';
            }

            $ordersContent[] = $member;
        }

        return $ordersContent;
    }
}
