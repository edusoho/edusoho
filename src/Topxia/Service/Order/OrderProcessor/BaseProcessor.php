<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;
use Exception;

class BaseProcessor {
	protected function afterCoinPay($coinEnabled, $priceType, $cashRate, $amount, $coinPayAmount, $payPassword)
	{
        if(!empty($coinPayAmount) && $coinPayAmount>0 && $coinEnabled) {
        	$user = $this->getAuthService()->getCurrentUser();
            $isRight = $this->getAuthService()->checkPayPassword($user["id"], $payPassword);
            if(!$isRight)
            	throw new Exception("支付密码不正确，创建订单失败!");
        }

        $coinPreferentialPrice = 0;
        if($priceType == "RMB") {
            $coinPreferentialPrice = $coinPayAmount/$cashRate;
        } else if ($priceType == "Coin") {
            $coinPreferentialPrice = $coinPayAmount;
        }

        return round($amount*1000 - $coinPreferentialPrice*1000)/1000;
	}

    protected function afterCouponPay($couponCode, $targetType, $targetId, $amount, $priceType, $cashRate)
    {
        $couponResult = $this->getCouponService()->checkCouponUseable($couponCode, $targetType, $targetId, $amount);   
        return $couponResult;
    }

    protected function getCouponService()
    {
        return ServiceKernel::instance()->createService('Coupon:Coupon.CouponService');
    }

	protected function getAuthService()
    {
        return ServiceKernel::instance()->createService('User.AuthService');
    }

    protected function getAppService()
    {
        return ServiceKernel::instance()->createService('CloudPlatform.AppService');
    }

}