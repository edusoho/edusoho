<?php

namespace Topxia\Service\PayCenter;

interface PayCenterService
{
	public function pay($payData);

	public function processOrder($payData, $lock=true);

	public function closeTrade($order);
}