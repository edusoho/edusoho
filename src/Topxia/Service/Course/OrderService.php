<?php
namespace Topxia\Service\Course;

interface OrderService
{

	public function getOrder($id);

	public function createOrder($order);

	public function payOrder($payData);

	public function canOrderPay($order);

	public function cancelOrder($id, $message = '');

	public function searchOrders($conditions, $sort = 'latest', $start, $limit);

    public function searchOrderCount($conditions);

    public function findOrderLogs($orderId);

}