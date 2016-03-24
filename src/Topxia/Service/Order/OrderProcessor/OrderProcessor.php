<?php
namespace Topxia\Service\Order\OrderProcessor;

interface OrderProcessor
{
    public function preCheck($targetId, $userId);

    public function doPaySuccess($success, $order);

    public function getOrderInfo($targetId, $fields);

    public function shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields);

    public function createOrder($orderInfo, $fields);

    public function getOrderBySn($sn);

    public function updateOrder($id, $fileds);

    public function getNote($targetId);

    public function getTitle($targetId);

    public function pay($payData);

    public function callbackUrl($order, $container);

    public function cancelOrder($id, $message, $data);

    public function createPayRecord($id, $payData);

    public function generateOrderToken();

    public function getOrderInfoTemplate();

    public function isTargetExist($targetId);
}
