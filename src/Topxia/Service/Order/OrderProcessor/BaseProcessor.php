<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\NumberToolkit;
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

    protected function getCoinSetting()
    {
        $coinSetting = $this->getSettingService()->get("coin");

        $coinEnable = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"]==1;

        $cashRate = 1;
        if($coinEnable && array_key_exists("cash_rate", $coinSetting)) {
            $cashRate = $coinSetting["cash_rate"];
        }

        $priceType = "RMB";
        if($coinEnable && !empty($coinSetting) && array_key_exists("price_type", $coinSetting)) {
            $priceType = $coinSetting["price_type"];
        }

        return array($coinEnable, $priceType, $cashRate);
    }

    protected function calculateCoinAmount($totalPrice, $priceType, $cashRate)
    {
        $user = $this->getUserService()->getCurrentUser();

        $account = $this->getCashAccountService()->getAccountByUserId($user["id"]);
        $accountCash = $account["cash"];

        $coinPayAmount = 0;

        $hasPayPassword = strlen($user['payPassword']) > 0;
        if($hasPayPassword){
            if ($priceType == "Coin") {
                if($totalPrice*100 > $accountCash*100) {
                    $coinPayAmount = $accountCash;
                } else {
                    $coinPayAmount = $totalPrice;
                }                
            } else if($priceType == "RMB") {
                if($totalPrice*100 > $accountCash/$cashRate*100) {
                    $coinPayAmount = $accountCash;
                } else {
                    $coinPayAmount = $totalPrice*$cashRate;
                }
            }
        }

        $totalPrice = NumberToolkit::roundUp($totalPrice);
        $coinPayAmount = NumberToolkit::roundUp($coinPayAmount);

        return array($totalPrice, $coinPayAmount, $account, $hasPayPassword);
    }

    protected function getCouponService()
    {
        return ServiceKernel::instance()->createService('Coupon:Coupon.CouponService');
    }

    protected function getCashAccountService()
    {
        return ServiceKernel::instance()->createService('Cash.CashAccountService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
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