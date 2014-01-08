<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\OrderService;
use Topxia\Common\ArrayToolkit;

class OrderServiceImpl extends BaseService implements OrderService
{

    public function getOrder($id)
    {
        return $this->getOrderDao()->getOrder($id);
    }

    public function getOrderBySn($sn)
    {
        return $this->getOrderDao()->getOrderBySn($sn);
    }

    public function findOrdersByIds(array $ids)
    {
        $orders = $this->getOrderDao()->findOrdersByIds($ids);
        return ArrayToolkit::index($orders, 'id');
    }

    public function createOrder($order)
    {
        $orderUser = $this->getCurrentUser();
        if (empty($orderUser)) {
            throw $this->createServiceException('用户未登录，不能创建订单。');
        }

        if (!empty($order['userId'])) {
            $orderUser = $this->getUserService()->getUser($order['userId']);
            if (empty($orderUser)) {
                throw $this->createServiceException("订单用户(#{$order['userId']})不存在，不能创建订单。");
            }
        }

        if (!ArrayToolkit::requireds($order, array('courseId', 'payment'))) {
            throw $this->createServiceException('创建订单失败：缺少参数。');
        }

        $order = ArrayToolkit::parts($order, array('courseId', 'payment', 'price', 'note'));

        $course = $this->getCourseService()->getCourse($order['courseId']);
        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        if ($course['status'] != 'published') {
            throw $this->createServiceException('课程处于关闭或未发布状态，不能创建订单！');
        }

        if (!in_array($order['payment'], array('none', 'alipay', 'alipaydouble', 'tenpay'))) {
            throw $this->createServiceException('创建订单失败：payment取值不正确。');
        }

        $order['sn'] = $this->generateOrderSn($order);
        $order['title'] = "购买课程《{$course['title']}》";
        $order['price'] = !isset($order['price']) ? $course['price'] : number_format($order['price'], 2, '.', '');;
        $order['status'] = 'created';

        if (intval($order['price']*100) == 0) {
            $order['payment'] = 'none';
        }

        $order['userId'] = $orderUser['id'];
        $order['createdTime'] = time();

        $order = $this->getOrderDao()->addOrder($order);

        $this->_createLog($order['id'], 'created', '创建订单');

        return $order;
    }

    public function payOrder($payData)
    {
        $order = $this->getOrderDao()->getOrderBySn($payData['sn']);
        if (empty($order)) {
            throw $this->createNotFoundException("订单({$response->getSn()})已被删除！");
        }

        if ($payData['status'] == 'success') {
            // 避免浮点数比较大小可能带来的问题，转成整数再比较。
            if (intval($payData['amount']*100) !== intval($order['price']*100)) {
                $message = sprintf('支付信息，校验失败：订单(%s)的金额(%s)与实际支付的金额(%s)不一致。', array($order['sn'], $order['price'], $payData['amount']));
                $this->_createLog($order['id'], 'pay_error', $message, $payData);
                throw \RuntimeException($message);
            }

            if ($this->canOrderPay($order)) {
                $this->getOrderDao()->updateOrder($order['id'], array(
                    'status' => 'paid',
                    'paidTime' => $payData['paidTime'],
                ));
                $this->_createLog($order['id'], 'pay_success', '付款成功', $payData);

                $info = array(
                    'orderId' => $order['id'],
                    'remark'  => empty($payData['memberRemark']) ? '' : $payData['memberRemark'],
                );
                $this->getCourseService()->becomeStudent($order['courseId'], $order['userId'], $info);
            } else {
                $this->_createLog($order['id'], 'pay_ignore', '订单已处理，付款被忽略', $payData);
            }
        } else {
            $this->_createLog($order['id'], 'pay_unknown', '', $payData);
        }

        return $this->getOrder($order['id']);
    }

    public function findOrderLogs($orderId)
    {
        $order = $this->getOrder($orderId);
        if(empty($order)){
            throw $this->createServiceException("订单不存在，获取订单日志失败！");
        }
        return $this->getOrderLogDao()->findLogsByOrderId($orderId);
    }

    private function _createLog($orderId, $type, $message = '', array $data = array())
    {
        $user = $this->getCurrentUser();

        $log = array(
            'orderId' => $orderId,
            'type' => $type,
            'message' => $message,
            'data' => json_encode($data),
            'userId' => $this->getCurrentUser()->id,
            'ip' => $this->getCurrentUser()->currentIp,
            'createdTime' => time()
        );

        return $this->getOrderLogDao()->addLog($log);
    }

    public function canOrderPay($order)
    {
        if (empty($order['status'])) {
            throw new \InvalidArgumentException();
        }
        return in_array($order['status'], array('created'));
    }

    public function cancelOrder($id, $message = '')
    {
        
    }

    public function findUserRefundCount($userId)
    {
        return $this->getOrderRefundDao()->findRefundCountByUserId($userId);
    }

    public function findUserRefunds($userId, $start, $limit)
    {
        return $this->getOrderRefundDao()->findRefundsByUserId($userId, $start, $limit);
    }

    public function searchRefunds($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = array_filter($conditions);
        $orderBy = array('createdTime', 'DESC');
        return $this->getOrderRefundDao()->searchRefunds($conditions, $orderBy, $start, $limit);
    }

    public function searchRefundCount($conditions)
    {
        $conditions = array_filter($conditions);
        return $this->getOrderRefundDao()->searchRefundCount($conditions);
    }


    public function applyRefundOrder($id, $expectedAmount = null, $reason = array())
    {
        $order = $this->getOrder($id);
        if (empty($order)) {
            throw $this->createNotFoundException();
        }

        if ($order['status'] != 'paid') {
            throw $this->createServiceException("订单#{$order['id']}，不能退款");
        }

        // 订单金额为０时，不能退款
        if (intval($order['price'] * 100) == 0) {
            $expectedAmount = 0;
        }

        $setting = $this->getSettingService()->get('refund');

        // 系统未设置退款期限，不能退款
        if (empty($setting) or empty($setting['maxRefundDays'])) {
            $expectedAmount = 0;
        }

        // 超出退款期限，不能退款
        if ( (time() - $order['createdTime']) > (86400 * $setting['maxRefundDays']) ) {
            $expectedAmount = 0;
        }

        $status = 'created';
        if (!is_null($expectedAmount)) {
            $expectedAmount = number_format($expectedAmount, 2, '.', '');
            if (intval($expectedAmount * 100) === 0) {
                $status = 'success';
            };
        }

        $refund = $this->getOrderRefundDao()->addRefund(array(
            'orderId' => $order['id'],
            'userId' => $order['userId'],
            'courseId' => $order['courseId'],
            'status' => $status,
            'expectedAmount' => $expectedAmount,
            'reasonType' => empty($reason['type']) ? 'other' : $reason['type'],
            'reasonNote' => empty($reason['note']) ? '' : $reason['note'],
            'updatedTime' => time(),
            'createdTime' => time(),
        ));
        
        $this->getOrderDao()->updateOrder($order['id'], array(
            'status' => ($refund['status'] == 'success') ? 'cancelled' : 'refunding',
            'refundId' => $refund['id'],
        ));

        if ($refund['status'] == 'success') {
            $this->getCourseService()->removeStudent($order['courseId'], $order['userId']);
            $this->_createLog($order['id'], 'refund_success', '订单退款成功(无退款金额)');
        } else {
            $this->getCourseService()->lockStudent($order['courseId'], $order['userId']);
            $this->_createLog($order['id'], 'refund_apply', '订单申请退款' . (is_null($expectedAmount) ? '' : "，期望退款{$expectedAmount}元"));
        }

        return $refund;
    }

    public function auditRefundOrder($id, $pass, $actualAmount = null, $note = '')
    {
        $order = $this->getOrder($id);
        if (empty($order)) {
            throw $this->createServiceException("订单(#{$id})不存在，退款确认失败");
        }

        $user = $this->getCurrentUser();
        if (!$user->isAdmin()) {
            throw $this->createServiceException("订单(#{$id})，你无权进行退款确认操作");
        }

        if ($order['status'] != 'refunding') {
            throw $this->createServiceException("当前订单(#{$order['id']})状态下，不能进行确认退款操作");
        }

        $refund = $this->getOrderRefundDao()->getRefund($order['refundId']);
        if (empty($refund)) {
            throw $this->createServiceException("当前订单(#{$order['id']})退款记录不存在，不能进行确认退款操作");
        }

        if ($refund['status'] != 'created') {
            throw $this->createServiceException("当前订单(#{$order['id']})退款记录状态下，不能进行确认退款操作款");
        }


        if ($pass == true) {
        	if (empty($actualAmount)) {
        		$actualAmount = 0;
        	}

        	$actualAmount = number_format((float)$actualAmount, 2, '.', '');

            $this->getOrderRefundDao()->updateRefund($refund['id'], array(
                'status' => 'success',
                'actualAmount' => $actualAmount,
                'updatedTime' => time(),
            ));

            $this->getOrderDao()->updateOrder($order['id'], array(
                'status' => 'refunded',
            ));

            if ($this->getCourseService()->isCourseStudent($order['courseId'], $order['userId'])) {
                $this->getCourseService()->removeStudent($order['courseId'], $order['userId']);
            }

            $this->_createLog($order['id'], 'refund_success', "退款申请(ID:{$refund['id']})已审核通过：{$note}");

        } else {
            $this->getOrderRefundDao()->updateRefund($refund['id'], array(
                'status' => 'failed',
                'updatedTime' => time(),
            ));

            $this->getOrderDao()->updateOrder($order['id'], array(
                'status' => 'paid',
            ));

            if ($this->getCourseService()->isCourseStudent($order['courseId'], $order['userId'])) {
                $this->getCourseService()->unlockStudent($order['courseId'], $order['userId']);
            }

            $this->_createLog($order['id'], 'refund_failed', "退款申请(ID:{$refund['id']})已审核未通过：{$note}");
        }

        $this->getLogService()->info('course_order', 'andit_refund', "审核退款申请#{$refund['id']}");

    }

    public function cancelRefundOrder($id)
    {
        $order = $this->getOrder($id);
        if (empty($order)) {
            throw $this->createServiceException("订单(#{$id})不存在，取消退款失败");
        }

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createServiceException("用户未登录，订单(#{$id})取消退款失败");
        }

        if ($order['userId'] != $user['id'] and !$user->isAdmin()) {
            throw $this->createServiceException("订单(#{$id})，你无权限取消退款");
        }

        if ($order['status'] != 'refunding') {
            throw $this->createServiceException("当前订单(#{$order['id']})状态下，不能取消退款");
        }

        $refund = $this->getOrderRefundDao()->getRefund($order['refundId']);
        if (empty($refund)) {
            throw $this->createServiceException("当前订单(#{$order['id']})退款记录不存在，不能取消退款");
        }

        if ($refund['status'] != 'created') {
            throw $this->createServiceException("当前订单(#{$order['id']})退款记录状态下，不能取消退款");
        }

        $this->getOrderRefundDao()->updateRefund($refund['id'], array(
            'status' => 'cancelled',
            'updatedTime' => time(),
        ));

        $this->getOrderDao()->updateOrder($order['id'], array(
            'status' => 'paid',
        ));

        if ($this->getCourseService()->isCourseStudent($order['courseId'], $order['userId'])) {
            $this->getCourseService()->unlockStudent($order['courseId'], $order['userId']);
        }

        $this->_createLog($order['id'], 'refund_cancel', "取消退款申请(ID:{$refund['id']})");
    }

    public function searchOrders($conditions, $sort = 'latest', $start, $limit)
    {
        $orderBy = array();
        if ($sort == 'latest') {
            $orderBy =  array('createdTime', 'DESC');
        } else {
            $orderBy = array('createdTime', 'DESC');
        }

        $conditions = $this->_prepareSearchConditions($conditions);
        $orders = $this->getOrderDao()->searchOrders($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($orders, 'id');
    }

    public function searchOrderCount($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);
        return $this->getOrderDao()->searchOrderCount($conditions);
    }

    private function _prepareSearchConditions($conditions)
    {
        $conditions = array_filter($conditions);
        
        if (isset($conditions['date'])) {
            $dates = array(
                'yesterday'=>array(
                    strtotime('yesterday'),
                    strtotime('today'),
                ),
                'today'=>array(
                    strtotime('today'),
                    strtotime('tomorrow'),
                ),
                'this_week' => array(
                    strtotime('Monday this week'),
                    strtotime('Monday next week'),
                ),
                'last_week' => array(
                    strtotime('Monday last week'),
                    strtotime('Monday this week'),
                ),
                'next_week' => array(
                    strtotime('Monday next week'),
                    strtotime('Monday next week', strtotime('Monday next week')),
                ),
                'this_month' => array(
                    strtotime('first day of this month midnight'), 
                    strtotime('first day of next month midnight'),
                ),
                'last_month' => array(
                    strtotime('first day of last month midnight'),
                    strtotime('first day of this month midnight'),
                ),
                'next_month' => array(
                    strtotime('first day of next month midnight'),
                    strtotime('first day of next month midnight', strtotime('first day of next month midnight')),
                ),
            );

            if (array_key_exists($conditions['date'], $dates)) {
                $conditions['paidStartTime'] = $dates[$conditions['date']][0];
                $conditions['paidEndTime'] = $dates[$conditions['date']][1];
                unset($conditions['date']);
            }
        }
        
        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
        }
        unset($conditions['keywordType']);
        unset($conditions['keyword']);

        if (isset($conditions['buyer'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['buyer']);
            $conditions['userId'] = $user ? $user['id'] : -1;
        }

        return $conditions;
    }

    private function generateOrderSn($order)
    {
        return  'C' . date('YmdHis', time()) . mt_rand(10000,99999);
    }

    private function getOrderDao()
    {
        return $this->createDao('Course.OrderDao');
    }

    private function getOrderLogDao()
    {
        return $this->createDao('Course.OrderLogDao');
    }

    private function getOrderRefundDao()
    {
        return $this->createDao('Course.OrderRefundDao');
    }

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }

    private function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

}