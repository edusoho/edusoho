<?php
namespace Topxia\Service\Order\OrderProcessor;

interface OrderProcessor 
{
	public function doPaySuccess($success, $order);
}