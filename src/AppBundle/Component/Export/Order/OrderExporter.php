<?php

namespace AppBundle\Component\Export\Order;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;
use Codeages\Biz\Framework\Order\Service\OrderService;

class OrderExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();

        return $user->isAdmin();
    }

    public function getCount()
    {
        return $this->getOrderService()->countOrders($this->conditions);
    }

    public function getTitles()
    {
        return array('订单号', '订单状态', '订单名称', '订单价格', '优惠码', '优惠金额', '虚拟币支付', '实付价格', '支付方式', '购买者', '姓名', '操作', '创建时间', '付款时间');
    }

    public function getContent($start, $limit)
    {
        $orders = $this->getOrderService()->searchOrders($this->conditions, array('created_time' => 'DESC'), $start, $limit);
        $userIds = ArrayToolkit::column($orders, 'user_id');

        $users = $this->getUserService()->findUsersByIds($userIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        $ordersContent = $this->handlerOrder($orders, $users, $profiles);

        return $ordersContent;
    }

    public function buildCondition($conditions)
    {
        $conditions = ArrayToolkit::parts($conditions,
            array(
                'startDateTime',
                'endDateTime',
                'status',
                'payment',
                'keywordType',
                'keyword',
            )
        );
        if (!empty($conditions['startDateTime']) && !empty($conditions['startDateTime'])) {
            $conditions['start_time'] = strtotime($conditions['startDateTime']);
            $conditions['end_time'] = strtotime($conditions['startDateTime']);
        }


        return $conditions;
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
            $member[] = $status[$order['status']];

            $member[] = $order['title'];

            $member[] = $order['price_amount'];

            if (!empty($order['coupon'])) {
                $member[] = $order['coupon'];
            } else {
                $member[] = '无';
            }

            $member[] = empty($order['couponDiscount']) ? '' : $order['couponDiscount'];
            $member[] = !empty($order['coinRate']) ? ($order['coinAmount'] / $order['coinRate']) : '0';
            $member[] = $order['pay_amount'];

            $orderPayment = empty($order['payment']) ? 'none' : $order['payment'];
            if (!empty($payment[$orderPayment])) {
                $member[] = $payment[$orderPayment];
            } else {
                $member[] = $payment['none'];
            }

            $member[] = $users[$order['user_id']]['nickname'];
            $member[] = $profiles[$order['user_id']]['truename'] ? $profiles[$order['user_id']]['truename'] : '-';

            if (preg_match('/管理员添加/', $order['title'])) {
                $member[] = '管理员添加';
            } else {
                $member[] = '-';
            }

            $member[] = date('Y-n-d H:i:s', $order['created_time']);

            if ($order['pay_time'] != 0) {
                $member[] = date('Y-n-d H:i:s', $order['pay_time']);
            } else {
                $member[] = '-';
            }

            $ordersContent[] = $member;
        }

        return $ordersContent;
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }
}
