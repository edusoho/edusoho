<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\CourseOrderService;
use Topxia\Common\ArrayToolkit;

class CourseOrderServiceImpl extends BaseService implements CourseOrderService
{

    public function createOrder($info)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createServiceException('用户未登录，不能创建订单');
        }

        if (!ArrayToolkit::requireds($info, array('courseId', 'payment'))) {
            throw $this->createServiceException('订单数据缺失，创建课程订单失败。');
        }

        $course = $this->getCourseService()->getCourse($info['courseId']);
        if (empty($course)) {
            throw $this->createServiceException('课程不存在，创建课程订单失败。');
        }

        $order = array();

        $order['userId'] = $user->id;
        $order['title'] = "购买课程《{$course['title']}》";
        $order['targetType'] = 'course';
        $order['targetId'] = $course['id'];
        $order['payment'] = $info['payment'];
        $order['amount'] = $course['price'];
        $order['snPrefix'] = 'C';

        if (!empty($info['coupon'])) {
            $order['couponCode'] = $info['coupon'];
        }

        if (!empty($info['note'])) {
            $order['data'] = array('note' => $info['note']);
        }

        $order = $this->getOrderService()->createOrder($order);
        if (empty($order)) {
            throw $this->createServiceException('创建课程订单失败！');
        }

        // 免费课程，就直接将订单置为已购买
        if (intval($order['amount']*100) == 0) {
            list($success, $order) = $this->getOrderService()->payOrder(array(
                'sn' => $order['sn'],
                'status' => 'success', 
                'amount' => $order['amount'], 
                'paidTime' => time()
            ));

            $info = array(
                'orderId' => $order['id'],
                'remark'  => empty($order['data']['note']) ? '' : $order['data']['note'],
            );
            $this->getCourseService()->becomeStudent($order['targetId'], $order['userId'], $info);
        }

        return $order;
    }

    public function doSuccessPayOrder($id)
    {
        $order = $this->getOrderService()->getOrder($id);
        if (empty($order) or $order['targetType'] != 'course') {
            throw $this->createServiceException('非课程订单，加入课程失败。');
        }

        $info = array(
            'orderId' => $order['id'],
            'remark'  => empty($order['data']['note']) ? '' : $order['data']['note'],
        );

        if (!$this->getCourseService()->isCourseStudent($order['targetId'], $order['userId'])) {
            $this->getCourseService()->becomeStudent($order['targetId'], $order['userId'], $info);
        }

        return ;
    }

    public function cancelRefundOrder($id)
    {
        $order = $this->getOrderService()->getOrder($id);
        if (empty($order) or $order['targetType'] != 'course') {
            throw $this->createServiceException('订单不存在，取消退款申请失败。');
        }

        $this->getOrderService()->cancelRefundOrder($id);

        if ($this->getCourseService()->isCourseStudent($order['targetId'], $order['userId'])) {
            $this->getCourseService()->unlockStudent($order['targetId'], $order['userId']);
        }
    }

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getOrderService()
    {
        return $this->createService('Order.OrderService');
    }

}