<?php
namespace Topxia\Service\Order\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\ExtensionManager;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Order\OrderService;
use Topxia\Service\Common\ServiceEvent;

class OrderServiceImpl extends BaseService implements OrderService
{
    public function getOrder($id)
    {
        return $this->getOrderDao()->getOrder($id);
    }

    public function getOrderBySn($sn, $lock = false)
    {
        return $this->getOrderDao()->getOrderBySn($sn, $lock);
    }

    public function getOrderByToken($token)
    {
        return $this->getOrderDao()->getOrderByToken($token);
    }

    public function findOrdersByIds(array $ids)
    {
        $orders = $this->getOrderDao()->findOrdersByIds($ids);
        return ArrayToolkit::index($orders, 'id');
    }

    public function findOrdersBySns(array $sns)
    {
        $orders = $this->getOrderDao()->findOrdersBySns($sns);
        return ArrayToolkit::index($orders, 'id');
    }

    public function createOrder($order)
    {
        if (!ArrayToolkit::requireds($order, array('userId', 'title', 'amount', 'targetType', 'targetId', 'payment'))) {
            throw $this->createServiceException('创建订单失败：缺少参数。');
        }
        $order = ArrayToolkit::parts($order, array(
            'userId',
            'title',
            'amount',
            'targetType',
            'targetId',
            'payment',
            'note',
            'snPrefix',
            'data',
            'couponCode',
            'coinAmount',
            'coinRate',
            'priceType',
            'totalPrice',
            'coupon',
            'couponDiscount',
            'discountId',
            'discount'
        ));

        $orderUser = $this->getUserService()->getUser($order['userId']);

        if (empty($orderUser)) {
            throw $this->createServiceException("订单用户(#{$order['userId']})不存在，不能创建订单。");
        }

        $payment = ExtensionManager::instance()->getDataDict('payment');
        $payment = array_keys($payment);
        if (!in_array($order['payment'], $payment)) {
            throw $this->createServiceException('创建订单失败：payment取值不正确。');
        }

        $order['sn'] = $this->generateOrderSn($order);
        unset($order['snPrefix']);

        if (!empty($order['couponCode'])) {
            $couponInfo = $this->getCouponService()->checkCouponUseable($order['couponCode'], $order['targetType'], $order['targetId'], $order['amount']);

            if ($couponInfo['useable'] != 'yes') {
                throw $this->createServiceException("优惠码不可用");
            }
        }

        unset($order['couponCode']);

        $order['amount'] = number_format($order['amount'], 2, '.', '');

        if (intval($order['amount'] * 100) == 0 && $order['payment'] != 'outside') {
            $order['payment'] = 'none';
        }

        $order['status']      = 'created';
        $order['createdTime'] = time();

        $order = $this->getOrderDao()->addOrder($order);

        $this->_createLog($order['id'], 'created', '创建订单');
        $this->getDispatcher()->dispatch('order.service.created', new ServiceEvent($order));
        return $order;
    }

    public function payOrder($payData)
    {
        $success = false;
        $order   = $this->getOrderDao()->getOrderBySn($payData['sn']);

        if (empty($order)) {
            throw $this->createServiceException("订单({$payData['sn']})已被删除，支付失败。");
        }

        if ($payData['status'] == 'success') {
            // 避免浮点数比较大小可能带来的问题，转成整数再比较。

            if (intval($payData['amount'] * 100) !== intval($order['amount'] * 100)) {
                $message = sprintf('订单(%s)的金额(%s)与实际支付的金额(%s)不一致，支付失败。', $order['sn'], $order['amount'], $payData['amount']);
                $this->_createLog($order['id'], 'pay_error', $message, $payData);
                throw $this->createServiceException($message);
            }

            if ($this->canOrderPay($order)) {
                $payFields = array(
                    'status'   => 'paid',
                    'paidTime' => $payData['paidTime']
                );

                !empty($payData['payment']) ? $payFields['payment'] = $payData['payment'] : '';

                $this->getOrderDao()->updateOrder($order['id'], $payFields);
                $this->_createLog($order['id'], 'pay_success', '付款成功', $payData);
                $success = true;
            } else {
                $this->_createLog($order['id'], 'pay_ignore', '订单已处理', $payData);
            }
        } else {
            $this->_createLog($order['id'], 'pay_unknown', '', $payData);
        }

        $order = $this->getOrder($order['id']);

        if ($success) {
            $this->getDispatcher()->dispatch('order.service.paid', new ServiceEvent($order));
        }

        return array($success, $order);
    }

    public function findOrderLogs($orderId)
    {
        $order = $this->getOrder($orderId);

        if (empty($order)) {
            throw $this->createServiceException("订单不存在，获取订单日志失败！");
        }

        return $this->getOrderLogDao()->findLogsByOrderId($orderId);
    }

    public function canOrderPay($order)
    {
        if (empty($order['status'])) {
            throw new \InvalidArgumentException();
        }

        return in_array($order['status'], array('created', 'cancelled'));
    }

    public function analysisCourseOrderDataByTimeAndStatus($startTime, $endTime, $status)
    {
        return $this->getOrderDao()->analysisCourseOrderDataByTimeAndStatus($startTime, $endTime, $status);
    }

    public function analysisPaidCourseOrderDataByTime($startTime, $endTime)
    {
        return $this->getOrderDao()->analysisPaidCourseOrderDataByTime($startTime, $endTime);
    }

    public function analysisPaidClassroomOrderDataByTime($startTime, $endTime)
    {
        return $this->getOrderDao()->analysisPaidClassroomOrderDataByTime($startTime, $endTime);
    }

    public function analysisExitCourseDataByTimeAndStatus($startTime, $endTime)
    {
        return $this->getOrderDao()->analysisExitCourseOrderDataByTime($startTime, $endTime);
    }

    public function analysisAmount($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);
        return $this->getOrderDao()->analysisAmount($conditions);
    }

    public function analysisCoinAmount($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);
        return $this->getOrderDao()->analysisCoinAmount($conditions);
    }

    public function analysisTotalPrice($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);
        return $this->getOrderDao()->analysisTotalPrice($conditions);
    }

    public function analysisAmountDataByTime($startTime, $endTime)
    {
        return $this->getOrderDao()->analysisAmountDataByTime($startTime, $endTime);
    }

    public function analysisCourseAmountDataByTime($startTime, $endTime)
    {
        return $this->getOrderDao()->analysisCourseAmountDataByTime($startTime, $endTime);
    }

    public function analysisClassroomAmountDataByTime($startTime, $endTime)
    {
        return $this->getOrderDao()->analysisClassroomAmountDataByTime($startTime, $endTime);
    }

    public function analysisVipAmountDataByTime($startTime, $endTime)
    {
        return $this->getOrderDao()->analysisVipAmountDataByTime($startTime, $endTime);
    }

    protected function generateOrderSn($order)
    {
        $prefix = empty($order['snPrefix']) ? 'E' : (string) $order['snPrefix'];
        return $prefix.date('YmdHis', time()).mt_rand(10000, 99999);
    }

    public function createOrderLog($orderId, $type, $message = '', array $data = array())
    {
        $order = $this->getOrder($orderId);

        if (empty($order)) {
            throw $this->createServiceException("订单不存在，获取订单日志失败！");
        }

        return $this->_createLog($orderId, $type, $message, $data);
    }

    protected function _createLog($orderId, $type, $message = '', array $data = array())
    {
        $user = $this->getCurrentUser();

        $log = array(
            'orderId'     => $orderId,
            'type'        => $type,
            'message'     => $message,
            'data'        => json_encode($data),
            'userId'      => $user->id,
            'ip'          => $user->currentIp,
            'createdTime' => time()
        );

        return $this->getOrderLogDao()->addLog($log);
    }

    public function cancelOrder($id, $message = '', $data = array())
    {
        $order = $this->getOrder($id);

        if (empty($order)) {
            throw $this->createServiceException('订单不存在，取消订单失败！');
        }

        if (!in_array($order['status'], array('created'))) {
            throw $this->createServiceException('当前订单状态不能取消订单！');
        }

        $payment = $this->getSettingService()->get("payment");

        if (isset($payment["enabled"]) && $payment["enabled"] == 1
            && isset($payment[$order["payment"]."_enabled"]) && $payment[$order["payment"]."_enabled"] == 1
            && isset($payment["close_trade_enabled"]) && $payment["close_trade_enabled"] == 1
        ) {
            $data = array_merge($data, $this->getPayCenterService()->closeTrade($order));
        }

        $order = $this->getOrderDao()->updateOrder($order['id'], array('status' => 'cancelled'));

        $this->_createLog($order['id'], 'cancelled', $message, $data);

        return $order;
    }

    public function createPayRecord($id, array $payData)
    {
        $order = $this->getOrder($id);
        $data  = $order['data'];

        if (!is_array($data)) {
            $data = json_decode($order['data'], true);
        }

        foreach ($payData as $key => $value) {
            $data[$key] = $value;
        }

        $fields = array('data' => $data);
        $order  = $this->updateOrder($id, $fields);
        $this->_createLog($order['id'], 'pay_create', '创建交易', $payData);
    }

    public function sumOrderPriceByTarget($targetType, $targetId)
    {
        return $this->getOrderDao()->sumOrderPriceByTargetAndStatuses($targetType, $targetId, array('paid', 'refunding', 'refunded'));
    }

    public function sumCouponDiscountByOrderIds($orderIds)
    {
        return $this->getOrderDao()->sumCouponDiscountByOrderIds($orderIds);
    }

    public function findUserRefundCount($userId)
    {
        return $this->getOrderRefundDao()->findRefundCountByUserId($userId);
    }

    public function findRefundsByIds(array $ids)
    {
        return $this->getOrderRefundDao()->findRefundsByIds($ids);
    }

    public function findUserRefunds($userId, $start, $limit)
    {
        return $this->getOrderRefundDao()->findRefundsByUserId($userId, $start, $limit);
    }

    public function searchRefunds($conditions, $sort, $start, $limit)
    {
        $conditions = array_filter($conditions);
        $orderBy    = array('createdTime', 'DESC');
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

        if (intval($order['amount'] * 100) == 0) {
            $expectedAmount = 0;
        }

        $setting = $this->getSettingService()->get('refund');

        // 系统未设置退款期限，不能退款

        if (empty($setting) || empty($setting['maxRefundDays'])) {
            $expectedAmount = 0;
        }

        // 超出退款期限，不能退款

        if ((time() - $order['createdTime']) > (86400 * $setting['maxRefundDays'])) {
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
            'orderId'        => $order['id'],
            'userId'         => $order['userId'],
            'targetType'     => $order['targetType'],
            'targetId'       => $order['targetId'],
            'status'         => $status,
            'expectedAmount' => $expectedAmount,
            'reasonType'     => empty($reason['type']) ? 'other' : $reason['type'],
            'reasonNote'     => empty($reason['note']) ? '' : $reason['note'],
            'updatedTime'    => time(),
            'createdTime'    => time(),
            'operator'       => 0
        ));

        $this->getOrderDao()->updateOrder($order['id'], array(
            'status'   => ($refund['status'] == 'success') ? 'cancelled' : 'refunding',
            'refundId' => $refund['id']
        ));

        if ($refund['status'] == 'success') {
            $this->_createLog($order['id'], 'refund_success', '订单退款成功(无退款金额)');
        } else {
            $this->_createLog($order['id'], 'refund_apply', '订单申请退款'.(is_null($expectedAmount) ? '' : "，期望退款{$expectedAmount}元"));
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

            $actualAmount = number_format((float) $actualAmount, 2, '.', '');

            $this->getOrderRefundDao()->updateRefund($refund['id'], array(
                'status'       => 'success',
                'operator'     => $user->id,
                'actualAmount' => $actualAmount,
                'updatedTime'  => time()
            ));

            $this->getOrderDao()->updateOrder($order['id'], array(
                'status' => 'refunded'
            ));

            $this->_createLog($order['id'], 'refund_success', "退款申请(ID:{$refund['id']})已审核通过：{$note}");
        } else {
            $this->getOrderRefundDao()->updateRefund($refund['id'], array(
                'status'      => 'failed',
                'operator'    => $user->id,
                'updatedTime' => time()
            ));

            $this->getOrderDao()->updateOrder($order['id'], array(
                'status' => 'paid'
            ));

            $this->_createLog($order['id'], 'refund_failed', "退款申请(ID:{$refund['id']})已审核未通过：{$note}");
        }

        $this->getLogService()->info('order', 'andit_refund', "审核退款申请#{$refund['id']}");

        return $pass;
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

        if ($order['userId'] != $user['id'] && !$user->isAdmin()) {
            throw $this->createServiceException("订单(#{$id})，你无权限取消退款");
        }

        if ($order['status'] != 'refunding') {
            throw $this->createServiceException("当前订单(#{$order['id']})状态下，不能取消退款");
        }

        $refund = $this->getOrderRefundDao()->getRefund($order['refundId']);

        if (empty($refund)) {
            throw $this->createServiceException("当前订单(#{$order['id']})退款记录不存在，不能取消退款");
        }

        $this->getOrderRefundDao()->updateRefund($refund['id'], array(
            'status'      => 'cancelled',
            'operator'    => $user->id,
            'updatedTime' => time()
        ));

        $this->getOrderDao()->updateOrder($order['id'], array(
            'status' => 'paid'
        ));
        $this->getLogService()->info('order', 'refund_cancel', "审核退款申请#{$refund['id']}");
        $this->_createLog($order['id'], 'refund_cancel', "取消退款申请(ID:{$refund['id']})");
    }

    public function searchOrders($conditions, $sort, $start, $limit)
    {
        $orderBy = array();

        if ($sort == 'early') {
            $orderBy = array('createdTime', 'ASC');
        } else {
            $orderBy = array('createdTime', 'DESC');
        }

        $conditions = $this->_prepareSearchConditions($conditions);
        $orders     = $this->getOrderDao()->searchOrders($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($orders, 'id');
    }

    public function searchBill($conditions, $sort, $start, $limit)
    {
        $orderBy = array();

        if ($sort == 'early') {
            $orderBy = array('createdTime', 'ASC');
        } else {
            $orderBy = array('createdTime', 'DESC');
        }

        $conditions = $this->_prepareSearchConditions($conditions);
        $orders     = $this->getOrderDao()->searchBill($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($orders, 'id');
    }

    public function countUserBillNum($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);
        return $this->getOrderDao()->countUserBillNum($conditions);
    }

    public function sumOrderAmounts($startTime, $endTime, array $courseId)
    {
        return $this->getOrderDao()->sumOrderAmounts($startTime, $endTime, $courseId);
    }

    public function searchOrderCount($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);
        return $this->getOrderDao()->searchOrderCount($conditions);
    }

    protected function _prepareSearchConditions($conditions)
    {
        $tmpConditions = array();

        if (isset($conditions['coinAmount'])) {
            $tmpConditions['coinAmount'] = $conditions['coinAmount'];
        }

        if (isset($conditions['amount'])) {
            $tmpConditions['amount'] = $conditions['amount'];
        }

        $conditions = array_filter($conditions);
        $conditions = array_merge($conditions, $tmpConditions);

        if (isset($conditions['date'])) {
            $dates = array(
                'yesterday'  => array(
                    strtotime('yesterday'),
                    strtotime('today')
                ),
                'today'      => array(
                    strtotime('today'),
                    strtotime('tomorrow')
                ),
                'this_week'  => array(
                    strtotime('Monday this week'),
                    strtotime('Monday next week')
                ),
                'last_week'  => array(
                    strtotime('Monday last week'),
                    strtotime('Monday this week')
                ),
                'next_week'  => array(
                    strtotime('Monday next week'),
                    strtotime('Monday next week', strtotime('Monday next week'))
                ),
                'this_month' => array(
                    strtotime('first day of this month midnight'),
                    strtotime('first day of next month midnight')
                ),
                'last_month' => array(
                    strtotime('first day of last month midnight'),
                    strtotime('first day of this month midnight')
                ),
                'next_month' => array(
                    strtotime('first day of next month midnight'),
                    strtotime('first day of next month midnight', strtotime('first day of next month midnight'))
                )
            );

            if (array_key_exists($conditions['date'], $dates)) {
                $conditions['paidStartTime'] = $dates[$conditions['date']][0];
                $conditions['paidEndTime']   = $dates[$conditions['date']][1];
                unset($conditions['date']);
            }
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
        }

        unset($conditions['keywordType']);
        unset($conditions['keyword']);

        if (isset($conditions['buyer'])) {
            $user                 = $this->getUserService()->getUserByNickname($conditions['buyer']);
            $conditions['userId'] = $user ? $user['id'] : -1;
        }

        return $conditions;
    }

    public function updateOrderCashSn($id, $cashSn)
    {
        $order = $this->getOrder($id);

        if (empty($order)) {
            throw $this->createServiceException('更新订单失败：订单不存在。');
        }

        if (empty($cashSn)) {
            throw $this->createServiceException('更新订单失败：支付流水号不存在。');
        }

        $this->getOrderDao()->updateOrder($id, array("cashSn" => $cashSn));
    }

    public function updateOrder($id, $orderFileds)
    {
        return $this->getOrderDao()->updateOrder($id, $orderFileds);
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getCardService()
    {
        return $this->createService('Card.CardService');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getOrderRefundDao()
    {
        return $this->createDao('Order.OrderRefundDao');
    }

    protected function getOrderDao()
    {
        return $this->createDao('Order.OrderDao');
    }

    protected function getOrderLogDao()
    {
        return $this->createDao('Order.OrderLogDao');
    }

    protected function getCouponService()
    {
        return $this->createService('Coupon.CouponService');
    }

    protected function getInviteRecordService()
    {
        return $this->createService('User.InviteRecordService');
    }

    protected function getPayCenterService()
    {
        return $this->createService('PayCenter.PayCenterService');
    }
}
