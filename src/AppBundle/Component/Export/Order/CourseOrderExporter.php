<?php

namespace AppBundle\Component\Export\Order;

use AppBundle\Common\ArrayToolkit;

class CourseOrderExporter extends OrderExporter
{
    protected $target = 'course';

    public function getTitles()
    {
        return array('订单号', '订单状态', '订单名称', '订单价格', '优惠码', '优惠金额', '虚拟币支付', '实付价格', '支付方式', '购买者', '姓名', '操作', '创建时间', '付款时间');
    }

    public function buildCondition($conditions)
    {
        $conditions['order_item_target_type'] = $this->target;

        if ($conditions['order_item_target_type'] != 'course') {
            return $conditions;
        }
        if (isset($conditions['courseSetTitle'])) {
            $conditions['order_item_title'] = $conditions['courseSetTitle'];
        }

        if (!empty($conditions['courseSetId'])) {
            $courses = $this->getCourseService()->findCoursesByCourseSetId($conditions['courseSetId']);
            $courseIds = ArrayToolkit::column($courses, 'courseSetId');
            $conditions['order_item_target_ids'] = empty($courseIds) ? array(-1) : $courseIds;
            unset($conditions['targetId']);
        }
        if (isset($conditions['buyer'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['buyer']);
            $conditions['user_id'] = $user ? $user['id'] : -1;
        }
        if (isset($conditions['mobile'])) {
            $user = $this->getUserService()->getUserByVerifiedMobile($conditions['mobile']);
            $conditions['user_id'] = $user ? $user['id'] : -1;
        }
        if (isset($conditions['email'])) {
            $user = $this->getUserService()->getUserByEmail($conditions['email']);
            $conditions['user_id'] = $user ? $user['id'] : -1;
        }
        if (!empty($conditions['startDateTime']) && !empty($conditions['startDateTime'])) {
            $conditions['start_time'] = strtotime($conditions['startDateTime']);
            $conditions['end_time'] = strtotime($conditions['startDateTime']);
        }

        return $conditions;
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

    protected function handlerOrder($orders, $users, $profiles)
    {
        $ordersContent = array();

        $payment = $this->container->get('codeages_plugin.dict_twig_extension')->getDict('payment');
        $status = array(
            'no_paid' => '未付款',
            'paid' => '已付款',
            'refunding' => '退款中',
            'refunded' => '已退款',
            'closed' => '已关闭',
        );

        foreach ($orders as $key => $order) {
            $member = array();
            $member[] = $order['sn'];
            $member[] = $status[$order['display_status']];

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
}
