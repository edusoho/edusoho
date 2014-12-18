<?php
namespace Topxia\Service\Order\OrderProcessor;

interface OrderProcessor 
{
	public function getRouter();

	public function doSuccessPayOrder($success, $order);
}