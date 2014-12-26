<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;

class BaseProcessor {
	protected function afterCoinPay($coinEnabled, $priceType, $cashRate, $coinPayAmount, $payPassword)
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

        return $coinPreferentialPrice;
	}

    protected function roundUp($value, $precision=2) 
    {
        $amt = explode(".", $value);
        if(strlen($amt[1]) > $precision) {
            $next = (int)substr($amt[1],$precision);
            $amt[1] = (float)(".".substr($amt[1],0,$precision));
            if($next != 0) {
                $rUp = "";
                for($x=1;$x<$precision;$x++) $rUp .= "0";
                $amt[1] = $amt[1] + (float)(".".$rUp."1");
            }
        }
        else {
            $amt[1] = (float)(".".$amt[1]);
        }
        return $amt[0]+$amt[1];
    }

	protected function getAuthService()
    {
        return ServiceKernel::instance()->createService('User.AuthService');
    }

}