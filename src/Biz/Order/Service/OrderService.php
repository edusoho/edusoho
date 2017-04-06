<?php

namespace Biz\Order\Service;

interface OrderService
{
    const TARGETTYPE_COURSE = 'course';

    const SNPREFIX_C = 'C';

    public function getOrder($id);

    public function getOrderBySn($sn, $lock = false);

    public function getOrderByToken($token);

    public function findOrdersByIds(array $ids);

    public function findOrdersBySns(array $sns);

    public function searchOrders($conditions, $sort, $start, $limit);

    public function countUserBillNum($conditions);

    public function sumOrderAmounts($startTime, $endTime, array $courseId);

    /**
     * @param $conditions
     *
     * @return mixed
     * @before searchOrderCount
     */
    public function countOrders($conditions);

    public function findOrderLogs($orderId);

    public function createOrder($order);

    public function payOrder($payData);

    /**
     * [createSystemOrder 创建系统内部订单，如VIP，免费加入，导入加入等].
     *
     * @param [type] $order [description]
     *
     * @return [type] [description]
     */
    public function createSystemOrder($order);

    public function canOrderPay($order);

    public function cancelOrder($id, $message = '', $data = array());

    public function sumOrderPriceByTarget($targetType, $targetId);

    public function sumCouponDiscountByOrderIds($orderIds);

    public function findUserRefundCount($userId);

    public function findRefundsByIds(array $ids);

    public function findUserRefunds($userId, $start, $limit);

    public function searchRefunds($conditions, $sort, $start, $limit);

    /**
     * @param $conditions
     *
     * @return mixed
     * @before  searchRefundCount
     */
    public function countRefunds($conditions);

    /**
     * @param $orderId
     *
     * @return mixed
     * @before findRefundByOrderId
     */
    public function getRefundByOrderId($orderId);

    /**
     * 申请退款
     * $expectedAmount, 0代表无需退款，NULL代表退款额度未知.
     */
    public function applyRefundOrder($id, $expectedAmount = null, $reason = array());

    /**
     * 审核退款申请.
     *
     * $pass, TRUE为通过退款, FALSE为退款失败
     * $actualAmount为实际退款金额
     */
    public function auditRefundOrder($id, $pass, $actualAmount = null, $note = '');

    public function cancelRefundOrder($id);

    public function analysisCourseOrderDataByTimeAndStatus($startTime, $endTime, $status);

    public function analysisPaidCourseOrderDataByTime($startTime, $endTime);

    public function analysisPaidClassroomOrderDataByTime($startTime, $endTime);

    public function analysisExitCourseDataByTimeAndStatus($startTime, $endTime);

    public function analysisAmount($conditions);

    public function analysisCoinAmount($conditions);

    public function analysisTotalPrice($conditions);

    public function analysisAmountDataByTime($startTime, $endTime);

    public function analysisCourseAmountDataByTime($startTime, $endTime);

    public function analysisClassroomAmountDataByTime($startTime, $endTime);

    public function analysisVipAmountDataByTime($startTime, $endTime);

    public function updateOrderCashSn($id, $cashSn);

    public function updateOrder($id, $orderFileds);

    public function createPayRecord($id, array $payDate);

    public function createOrderLog($orderId, $type, $message = '', array $data = array());

    public function analysisPaidOrderGroupByTargetType($startTime, $groupBy);

    public function analysisOrderDate($conditions);

    public function findOrderLogsByOrderIds(array $orderIds);

    public function findOrderRefundsByOrderIds(array $orderIds);
}
