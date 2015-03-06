<?php
namespace Topxia\MobileBundleV2\Processor;

interface OrderProcessor
{
	public function validateIAPReceipt();

	public function payCourse();

	/*
	* (float) amount 充值金额
	* (String)  payType  iap, alipay
	*/
	public function buyCoin();
}