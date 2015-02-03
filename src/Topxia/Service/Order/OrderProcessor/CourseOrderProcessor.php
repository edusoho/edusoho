<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\NumberToolkit;
use Exception;

class CourseOrderProcessor extends BaseProcessor implements OrderProcessor
{
	protected $router = "course_show";

	public function getRouter() {
		return $this->router;
	}

	public function getOrderInfo($targetId, $fields)
	{
		$course = $this->getCourseService()->getCourse($targetId);
        if(empty($course)) {
            throw new Exception("找不到要购买课程!");
        }

        $users = $this->getUserService()->findUsersByIds($course['teacherIds']);
        $coinSetting = $this->getSettingService()->get("coin");
        
        $totalPrice = 0;

        if(!isset($coinSetting["coin_enabled"]) 
            || !$coinSetting["coin_enabled"]) {
        	$totalPrice = $course["price"];
        	return array(
				'totalPrice' => $totalPrice,
				'targetId' => $targetId,
            	'targetType' => "course",

				'courses' => empty($course) ? null : array($course),
				'users' => $users,
        	);
        }

        $coinEnabled = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"];
        $cashRate = 1;
        if($coinEnabled && array_key_exists("cash_rate", $coinSetting)) {
            $cashRate = $coinSetting["cash_rate"];
        }

        $priceType = "RMB";
        if(array_key_exists("price_type", $coinSetting)) {
            $priceType = $coinSetting["price_type"];
        }

        $user = $this->getUserService()->getCurrentUser();
        $account = $this->getCashAccountService()->getAccountByUserId($user["id"]);
        $accountCash = $account["cash"];

        $coinPayAmount = 0;

        $hasPayPassword = strlen($user['payPassword']) > 0;
        if ($priceType == "Coin") {
            $totalPrice = $course["coinPrice"];
            if($hasPayPassword && $totalPrice*100 > $accountCash*100) {
                $coinPayAmount = $accountCash;
            } else if($hasPayPassword) {
                $coinPayAmount = $totalPrice;
            }                
        } else if($priceType == "RMB") {
            $totalPrice = $course["price"];
            if($totalPrice*100 > $accountCash/$cashRate*100) {
                $coinPayAmount = $accountCash;
            } else {
                $coinPayAmount = $totalPrice*$cashRate;
            }
        }

        $coinPayAmount = NumberToolkit::roundUp($coinPayAmount);
        return array(
            'courses' => empty($course) ? null : array($course),
            'users' => empty($users) ? null : $users,
            
            'totalPrice' => $totalPrice,
            'targetId' => $targetId,
            'targetType' => "course",
            'cashRate' => $cashRate,
            'priceType' => $priceType,
            'account' => $account,
            'hasPayPassword' => $hasPayPassword,
            'coinPayAmount' => $coinPayAmount,
        );
	}

	public function shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields)
	{
        $totalPrice = 0;
        $course = $this->getCourseService()->getCourse($targetId);
        if($priceType == "RMB") {
            $totalPrice = $course["price"];
        } else if ($priceType == "Coin") {
            $totalPrice = $course["coinPrice"];
        }

        if($totalPrice != $fields['totalPrice']) {
            throw new Exception("实际价格不匹配，不能创建订单!");
        }

        $totalPrice = (float)$totalPrice;
        $amount = $totalPrice;

        //优惠码优惠价格
        $couponApp = $this->getAppService()->findInstallApp("Coupon");
        $couponSetting = $this->getSettingService()->get("coupon");
        if(!empty($couponApp) && isset($couponSetting["enabled"]) && $couponSetting["enabled"] == 1 && $fields["couponCode"] && trim($fields["couponCode"]) != "") {
            $couponResult = $this->afterCouponPay(
                $fields["couponCode"], 
                $targetId, 
                $totalPrice, 
                $priceType, 
                $cashRate
            );
            if(isset($couponResult["useable"]) && $couponResult["useable"]=="yes" && isset($couponResult["afterAmount"])){
                $amount = $couponResult["afterAmount"];
            }
        }

        //虚拟币优惠价格
        if(array_key_exists("coinPayAmount", $fields)) {
            $amount = $this->afterCoinPay(
                $coinEnabled, 
                $priceType, 
                $cashRate, 
                $amount,
                $fields['coinPayAmount'], 
                $fields["payPassword"]
            );
        }

        if ($priceType == "Coin") {
            $amount = $amount/$cashRate;
        }
        if($amount<0){
            $amount = 0;
        }
        $amount = NumberToolkit::roundUp($amount);
        return array(
        	$amount, 
        	$totalPrice, 
        	empty($couponResult) ? null : $couponResult,
        );
	}

	public function createOrder($orderInfo, $fields) 
	{
		return $this->getCourseOrderService()->createOrder($orderInfo);
	}

	private function afterCouponPay($couponCode, $targetId, $amount, $priceType, $cashRate)
	{
		$couponResult = $this->getCouponService()->checkCouponUseable($couponCode, "course", $targetId, $amount);
        return $couponResult;
	}

	public function doPaySuccess($success, $order) {
        if (!$success) {
            return ;
        }

        $this->getCourseOrderService()->doSuccessPayOrder($order['id']);

        return ;
    }

    protected function getCouponService()
    {
        return ServiceKernel::instance()->createService('Coupon:Coupon.CouponService');
    }

    protected function getAppService()
    {
        return ServiceKernel::instance()->createService('CloudPlatform.AppService');
    }

    protected function getCourseService()
    {
    	return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getCashAccountService()
    {
        return ServiceKernel::instance()->createService('Cash.CashAccountService');
    }

	protected function getCourseOrderService() {
		return ServiceKernel::instance()->createService("Course.CourseOrderService");
	}
}