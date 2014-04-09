<?php
namespace Topxia\Service\Course;

interface OrderService
{
// <<<<<<< HEAD

// 	public function getOrder($id);

// 	public function getOrderBySn($sn);

// 	public function getOrdersByPromoCode($code);

// 	public function findOrdersByIds(array $ids);

// 	public function findOrdersByPromoCodes(array $codes);

// 	public function findOrderssByPromoCodes(array $codes);

// 	public function searchOrders($conditions, $sort = 'latest', $start, $limit);

//     public function searchOrderCount($conditions);

//     public function findOrderLogs($orderId);

// 	public function createOrder($order);

// 	public function payOrder($payData);

// 	public function canOrderPay($order);

// 	/**
// 	 * [cancelOrder description]
// 	 * @param  [type] $id
// 	 * @param  string $message
// 	 * @return [type]
// 	 */
// 	public function cancelOrder($id, $message = '');

// 	public function findUserRefundCount($userId);

// 	public function findUserRefunds($userId, $start, $limit);

// 	public function searchRefunds($conditions, $sort = 'latest', $start, $limit);

//     public function searchRefundCount($conditions);

// 	/**
// 	 * 申请退款
// 	 * $expectedAmount, 0代表无需退款，NULL代表退款额度未知
// 	 */
// 	public function applyRefundOrder($id, $expectedAmount = null, $reason = array());

// 	/**
// 	 * 审核退款申请
// 	 * 
// 	 * $pass, TRUE为通过退款, FALSE为退款失败
// 	 * $actualAmount为实际退款金额
// 	 */
// 	public function auditRefundOrder($id, $pass, $actualAmount = null, $note = '');

// =======
// >>>>>>> origin/master
	public function cancelRefundOrder($id);
}