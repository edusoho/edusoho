<?php
namespace Topxia\Service\Order\OrderProcessor;

interface OrderProcessor 
{
    public function preCheck($targetId, $userId);

	public function doPaySuccess($success, $order);

	public function getOrderInfo($targetId, $fields);

	public function shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields);

	public function createOrder($orderInfo, $fields);

}