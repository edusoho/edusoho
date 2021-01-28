<?php

namespace AppBundle\Component\Export\Order;

use AppBundle\Common\MathToolkit;
use AppBundle\Component\Export\Exporter;
use Biz\User\Service\UserService;
use Codeages\Biz\Order\Service\OrderService;

class OrderExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();

        if ($user->isAdmin()) {
            return true;
        }

        if (isset($this->parameter['courseId'])) {
            $course = $this->getCourseService()->tryManageCourse($this->parameter['courseId']);
            $courseSetting = $this->getSettingService()->get('course');
            if (!empty($courseSetting['teacher_search_order'])) {
                return true;
            }
        }

        return false;
    }

    public function getCount()
    {
        return $this->getOrderService()->countOrders($this->conditions);
    }

    private $statusMap = null;

    private $displayStatusDict = null;

    private $paymentDict = null;

    protected $target = 'course';

    public function getTitles()
    {
        return array('order.id', 'order.product_name', 'order.status', 'order.product_price', 'order.deduct_amount', 'order.price', 'order.coin_amount', 'order.cash_amount', 'order.payment_pattern', 'order.source', 'order.buyer.username', 'order.buyer.true_name', 'order.buyer.email', 'order.buyer.contact', 'order.created_time', 'order.paid_time');
    }

    public function buildCondition($conditions)
    {
        if (!empty($conditions['orderItemType'])) {
            $conditions['order_item_target_type'] = $conditions['orderItemType'];
        }

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
        }

        if (!empty($conditions['startDateTime'])) {
            $conditions['start_time'] = strtotime($conditions['startDateTime']);
        }

        if (!empty($conditions['endDateTime'])) {
            $conditions['end_time'] = strtotime($conditions['endDateTime']);
        }

        if (isset($conditions['buyer'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['buyer']);
            $conditions['user_id'] = $user ? $user['id'] : -1;
        }

        if (!empty($conditions['displayStatus'])) {
            $conditions['statuses'] = $this->container->get('web.twig.order_extension')->getOrderStatusFromDisplayStatus($conditions['displayStatus'], 1);
        }

        return $conditions;
    }

    public function getContent($start, $limit)
    {
        $orders = $this->getOrderService()->searchOrders($this->conditions, array('created_time' => 'DESC'), $start, $limit);
        $userIds = array_column($orders, 'user_id');

        $users = $this->getUserService()->findUsersByIds($userIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        $ordersContent = $this->handlerOrder($orders, $users, $profiles);

        return $ordersContent;
    }

    protected function handlerOrder($orders, $users, $profiles)
    {
        $ordersContent = array();
        $source = $this->container->get('codeages_plugin.dict_twig_extension')->getDict('source');
        foreach ($orders as $key => $order) {
            $member = array();
            // 订单号
            $member[] = $order['sn']."\t";
            // 订单名称
            $member[] = $order['title'];
            // 订单状态
            $member[] = $this->getExportStatus($order['status']);
            // 总价
            $member[] = MathToolkit::simple($order['price_amount'], 0.01);
            // 优惠金额
            $member[] = MathToolkit::simple($order['price_amount'] - $order['pay_amount'], 0.01);
            // 实付总额
            $member[] = MathToolkit::simple($order['pay_amount'], 0.01);
            // 虚拟币支付
            $member[] = MathToolkit::simple($order['paid_coin_amount'], 0.01);
            // 现金支付
            $member[] = MathToolkit::simple($order['paid_cash_amount'], 0.01);
            // 支付方式
            $member[] = $this->getExportPayment($order['payment']);

            //渠道
            $member[] = empty($source[$order['source']]) ? '--' : $source[$order['source']];
            //用户名
            $member[] = $users[$order['user_id']]['nickname'];
            //真实姓名
            $member[] = $profiles[$order['user_id']]['truename'] ? $profiles[$order['user_id']]['truename'] : '-';
            //邮箱
            $member[] = $users[$order['user_id']]['email'];
            //联系电话
            $member[] = $users[$order['user_id']]['verifiedMobile'];
            //创建时间
            $member[] = date('Y-n-d H:i:s', $order['created_time']);
            //付款时间
            if ($order['pay_time'] > 0) {
                $member[] = date('Y-n-d H:i:s', $order['pay_time']);
            } else {
                $member[] = '-';
            }

            $ordersContent[] = $member;

            unset($member);
        }

        return $ordersContent;
    }

    public function buildParameter($conditions)
    {
        $parameter = parent::buildParameter($conditions);
        if (!empty($conditions['courseId'])) {
            $parameter['courseId'] = $conditions['courseId'];
        }

        return $parameter;
    }

    private function getExportStatus($orderStatus)
    {
        if (!$this->statusMap) {
            $this->statusMap = $this->container->get('web.twig.order_extension')->getStatusMap();
        }

        if (!$this->displayStatusDict) {
            $this->displayStatusDict = $this->container->get('codeages_plugin.dict_twig_extension')->getDict('orderDisplayStatus');
        }

        return isset($this->statusMap[$orderStatus]) && isset($this->displayStatusDict[$this->statusMap[$orderStatus]]) ? $this->displayStatusDict[$this->statusMap[$orderStatus]] : $orderStatus;
    }

    private function getExportPayment($payment)
    {
        if (!$this->paymentDict) {
            $this->paymentDict = $this->container->get('codeages_plugin.dict_twig_extension')->getDict('payment');
        }

        return isset($this->paymentDict[$payment]) ? $this->paymentDict[$payment] : $payment;
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return parent::getUserService();
    }
}
