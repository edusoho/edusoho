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

	/*
	* (float) amount 金额
	* (String) sn 订单流水号
	* (String) status success 表示成功
	* (String)  payType  iap, alipay
	*/
	private function coinPayNotify();
}