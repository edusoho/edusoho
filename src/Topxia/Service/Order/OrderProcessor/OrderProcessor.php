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

    public function getOrderMessage($order);

    public function updateOrder($id, $fileds);

    public function requestParams($order, $container);

    public function getNote($targetId);

    public function getTitle($targetId);

    public function pay($payData);

    //public function cancelOrder($id, $message, $data);

}
