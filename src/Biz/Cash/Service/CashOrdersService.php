<?php

namespace Biz\Cash\Service;

interface CashOrdersService
{
    public function addOrder($order);

    public function getOrder($id);

    public function getOrderBySn($sn, $lock = false);

    public function getOrderByToken($token);

    public function cancelOrder($id, $message, $data);

    public function payOrder($payData);

    public function closeOrders();

    public function canOrderPay($order);

    public function updateOrder($id, $fileds);

    public function searchOrders($conditions, $orderBy, $start, $limit);

    public function searchOrdersCount($conditions);

    public function getLogsByOrderId($orderId);

    public function analysisAmount($conditions);

    public function createPayRecord($id, array $payDate);
}
