<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\CourseOrderService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceKernel;

class CourseOrderServiceImpl extends BaseService implements CourseOrderService
{
    public function cancelOrder($id)
    {
        $this->getOrderService()->cancelOrder($id);
    }

    private function cancelOldOrders($course, $user)
    {
        $conditions = array(
            'userId' => $user['id'],
            'status' => 'created',
            'targetType' => 'course',
            'targetId' => $course['id'],
        );
        $count = $this->getOrderService()->searchOrderCount($conditions);

        if ($count == 0) {
            return ;
        }

        $oldOrders = $this->getOrderService()->searchOrders($conditions, array('createdTime', 'DESC'), 0, $count);

        foreach ($oldOrders as $order) {
            $this->getOrderService()->cancelOrder($order['id'], '系统自动取消');
        }

    }

    public function createOrder($info)
    {
        $connection = ServiceKernel::instance()->getConnection();
        try {
            $connection->beginTransaction();
            
            $user = $this->getCurrentUser();
            if (!$user->isLogin()) {
                throw $this->createServiceException('用户未登录，不能创建订单');
            }

            if (!ArrayToolkit::requireds($info, array('targetId', 'payment'))) {
                throw $this->createServiceException('订单数据缺失，创建课程订单失败。');
            }

            // 获得锁
            $user = $this->getUserService()->getUser($user['id'], true);

            if ($this->getCourseService()->isCourseStudent($info['targetId'], $user['id'])) {
                throw $this->createServiceException('已经是课程学员，操作失败。');
            }

            $course = $this->getCourseService()->getCourse($info['targetId']);
            if (empty($course)) {
                throw $this->createServiceException('课程不存在，操作失败。');
            }

            $this->cancelOldOrders($course, $user);

            $order = array();

            $order['userId'] = $user['id'];
            $order['title'] = "购买课程《{$course['title']}》";
            $order['targetType'] = 'course';
            $order['targetId'] = $course['id'];
            $order['payment'] = $info['payment'];
            $order['amount'] = empty($info['amount'])? 0 : $info['amount'];
            $order['priceType'] = $info['priceType'];
            $order['totalPrice'] = $info["totalPrice"];
            $order['coinRate'] = $info['coinRate'];
            $order['coinAmount'] = $info['coinAmount'];

            $courseSetting=$this->getSettingService()->get('course',array());

            if (array_key_exists("coursesPrice", $courseSetting)) {
                $notShowPrice = $courseSetting['coursesPrice'];
            }else{
                $notShowPrice = 0;
            }

            if($notShowPrice == 1) {
                $order['amount'] = 0;
                $order['totalPrice'] = 0;
            }

            if($order['amount'] > 0){
                //如果是限时打折，判断是否在限免期，如果是，则Amout为0
                if($course['freeStartTime'] < time() &&  $course['freeEndTime'] > time() ){
                    $order['amount'] = 0;
                    $order['totalPrice'] = 0;
                }
            }

            $order['snPrefix'] = 'C';

            if (!empty($info['coupon'])) {
                $order['coupon'] = $info['coupon'];
                $order['couponDiscount'] = $info['couponDiscount'];
            }

            if (!empty($info['note'])) {
                $order['data'] = array('note' => $info['note']);
            }

            $order = $this->getOrderService()->createOrder($order);
            if (empty($order)) {
                throw $this->createServiceException('创建课程订单失败！');
            }

            // 免费课程，就直接将订单置为已购买
            if (intval($order['amount']*100) == 0 && intval($order['coinAmount']*100) == 0 && empty($order['coupon'])) {
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

            $connection->commit();

            return $order;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

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

    public function applyRefundOrder($id, $amount, $reason, $container)
    {
        $order = $this->getOrderService()->getOrder($id);
        if (empty($order)) {
            throw $this->createServiceException('订单不存在，不能申请退款。');
        }
        $refund = $this->getOrderService()->applyRefundOrder($id, $amount, $reason);

        if ($refund['status'] == 'created') {
            $this->getCourseService()->lockStudent($order['targetId'], $order['userId']);

            $setting = $this->getSettingService()->get('refund');
            $message = empty($setting) or empty($setting['applyNotification']) ? '' : $setting['applyNotification'];
            if ($message) {
                $course = $this->getCourseService()->getCourse($order["targetId"]);
                $courseUrl = $container->get('router')->generate('course_show', array('id' => $course['id']));
                $variables = array(
                    'course' => "<a href='{$courseUrl}'>{$course['title']}</a>"
                );
                $message = StringToolkit::template($message, $variables);
                $this->getNotificationService()->notify($refund['userId'], 'default', $message);
            }

        } elseif ($refund['status'] == 'success') {
            $this->getCourseService()->removeStudent($order['targetId'], $order['userId']);
        }

        return $refund;

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

    public function updateOrder($id, $orderFileds) 
    {
        $orderFileds = array(
            'priceType' => $orderFileds["priceType"],
            'totalPrice' => $orderFileds["totalPrice"],
            'amount' => $orderFileds['amount'],
            'coinRate' => $orderFileds['coinRate'],
            'coinAmount' => $orderFileds['coinAmount'],
            'userId' => $orderFileds["userId"],
            'payment' => $orderFileds["payment"],
            'targetId' => $orderFileds["courseId"]
        );

        return $this->getOrderService()->updateOrder($id, $orderFileds);
    }
    
    protected function getCashService()
    {
        return $this->createService('Cash.CashService');
    }

    protected function getCashAccountService()
    {
        return $this->createService('Cash.CashAccountService');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order.OrderService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    private function getNotificationService()
    {
        return $this->createService('User.NotificationService');
    }

}