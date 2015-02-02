<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\NumberToolkit;
use Exception;

class VipOrderProcessor extends BaseProcessor implements OrderProcessor
{
	protected $router = "vip";

	public function getRouter() {
		return $this->router;
	}

	public function getOrderInfo($targetId, $fields)
	{
        $user = $this->getUserService()->getCurrentUser();

        $level = $this->getLevelService()->getLevel($fields['targetId']);
        if(empty($level)) {
            throw new Exception("找不到会员等级!");
        }

        $member = $this->getVipService()->getMemberByUserId($user->id);
        if ($member) {
            if(array_key_exists("buyType", $fields) && $fields['buyType'] == "upgrade"){
                $buyType = "upgrade";
            } else {
                $buyType = "renew";
            }
        } else {
            $buyType = "new";
        }


        $levelPrice = array(
        	'month' => $level['monthPrice'],
        	'year' => $level['yearPrice']
        );

        $coinSetting = $this->getSettingService()->get("coin");

        $cashRate = 1;
        if(array_key_exists("cash_rate", $coinSetting)) {
            $cashRate = $coinSetting["cash_rate"];
        }

        $priceType = "RMB";
        if(array_key_exists("price_type", $coinSetting)) {
            $priceType = $coinSetting["price_type"];
        }

        if($buyType == "upgrade") {
            $totalPrice = $this->getVipService()->calUpgradeMemberAmount($user->id, $level['id']);
        }else{
            if(!ArrayToolkit::requireds($fields, array("unit", "duration"))) {
                throw new Exception("参数不正确!");
            }

            $unitType = $fields['unit'];
            $duration = $fields['duration'];

            $unitPrice = $levelPrice[$unitType];
            if ($priceType == "Coin") {
                $unitPrice = NumberToolkit::roundUp($unitPrice * $cashRate);
            }

            $totalPrice = $unitPrice * $duration;
        }

        

        if(!array_key_exists("coin_enabled", $coinSetting) 
            || !$coinSetting["coin_enabled"]) {
        	return array(
				'totalPrice' => $totalPrice,
				'targetId' => $targetId,
            	'targetType' => "vip",

				'level' => empty($level) ? null : $level,
				'unitType' => empty($unitType) ? null : $unitType,
				'duration' => empty($duration) ? null : $duration,
				'buyType' => empty($buyType) ? null : $buyType,
        	);
        }

        $account = $this->getCashAccountService()->getAccountByUserId($user["id"]);
        $accountCash = $account["cash"];

        $coinPayAmount = 0;

        $hasPayPassword = strlen($user['payPassword']) > 0;
        if ($priceType == "Coin") {
            if($hasPayPassword && $totalPrice*100 > $accountCash*100) {
                $coinPayAmount = $accountCash;
            } else if($hasPayPassword) {
                $coinPayAmount = $totalPrice;
            }                
        } else if($priceType == "RMB") {
            if($totalPrice*100 > $accountCash/$cashRate*100) {
                $coinPayAmount = $accountCash;
            } else {
                $coinPayAmount = $totalPrice*$cashRate;
            }
        }

        $totalPrice = NumberToolkit::roundUp($totalPrice);
        $coinPayAmount = NumberToolkit::roundUp($coinPayAmount);

        return array(
            'level' => empty($level) ? null : $level,
			'unitType' => empty($unitType) ? null : $unitType,
            'duration' => empty($duration) ? null : $duration,
			'buyType' => empty($buyType) ? null : $buyType,

            'totalPrice' => $totalPrice,
            'targetId' => $targetId,
            'targetType' => "vip",
            'cashRate' => $cashRate,
            'priceType' => $priceType,
            'account' => $account,
            'hasPayPassword' => $hasPayPassword,
            'coinPayAmount' => $coinPayAmount,
        );
        
	}

	public function shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $orderData)
	{
		$totalPrice = 0;

		if (!ArrayToolkit::requireds($orderData, array('buyType', 'targetId', 'unitType', 'duration'))) {
            throw new Exception('订单数据缺失，创建会员订单失败。');
        }

        if (!in_array($orderData['buyType'], array('new', 'renew', 'upgrade'))) {
            throw new Exception('购买类型不正确，创建会员订单失败。');
        }

        if(!(array_key_exists("buyType", $orderData) && $orderData["buyType"]=="upgrade")){
            $orderData['duration'] = intval($orderData['duration']);
            if (empty($orderData['duration'])) {
                throw new Exception('会员开通时长不正确，创建会员订单失败。');
            }

            if (!in_array($orderData['unitType'], array('month', 'year'))) {
                throw new Exception('付费方式不正确，创建会员订单失败。');
            }
        }

        $level = $this->getLevelService()->getLevel($orderData['targetId']);
        if (empty($level)) {
            throw new Exception('会员等级不存在，创建会员订单失败。');
        }
        if (empty($level['enabled'])) {
            throw new Exception('会员等级已关闭，创建会员订单失败。');
        }

        $currentUser = $this->getLevelService()->getCurrentUser();
        if(array_key_exists("buyType", $orderData) && $orderData["buyType"]=="upgrade"){
            $totalPrice = $this->getVipService()->calUpgradeMemberAmount($currentUser->id, $level['id']);
        } else {
            $unitPrice = $level[$orderData['unitType'] . 'Price'];
            if ($priceType == "Coin") {
                $unitPrice = NumberToolkit::roundUp($unitPrice * $cashRate);
            }
            $totalPrice = $unitPrice * $orderData['duration'];
        }

        //$totalPrice = intval($totalPrice*1000)/1000;
        $amount = $totalPrice;
        //优惠码优惠价格
        $couponApp = $this->getAppService()->findInstallApp("Coupon");
        $couponSetting = $this->getSettingService()->get("coupon");
        if(!empty($couponApp) && isset($couponSetting["enabled"]) && $couponSetting["enabled"] == 1 && $orderData["couponCode"] && trim($orderData["couponCode"]) != "") {
            $couponResult = $this->afterCouponPay(
                $orderData["couponCode"], 
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
        if(array_key_exists("coinPayAmount", $orderData)) {
            $amount = $this->afterCoinPay(
            	$coinEnabled, 
            	$priceType, 
            	$cashRate, 
                $amount,
            	$orderData['coinPayAmount'], 
            	$orderData["payPassword"]
            );
        } 

        if ($priceType == "Coin") {
            $amount = $amount/$cashRate;
        }

        if($amount<=0) {
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
		unset($orderInfo['coupon']);
		unset($orderInfo['couponDiscount']);
		
        $level = $this->getLevelService()->getLevel($orderInfo['targetId']);

        $unitNames = array('month' => '个月', 'year' => '年');
        if(array_key_exists("buyType", $fields) && $fields["buyType"] == "upgrade") {
            $orderInfo['title'] = "升级会员到 {$level['name']}";
            $orderInfo['snPrefix'] = 'M';
        } else {
            $orderInfo['title'] = ($fields['buyType'] == 'renew' ? '续费' : '购买') .  "{$level['name']} x {$fields['duration']}{$unitNames[$fields['unitType']]}{$level['name']}会员";
            $orderInfo['snPrefix'] = 'V';
        }
        $orderInfo['targetType'] = 'vip';
        $orderInfo['data'] = $fields;

		return $this->getOrderService()->createOrder($orderInfo);
	}

	public function doPaySuccess($success, $order) {
        if (!$success) {
            return ;
        }
        if ($order['data']['buyType'] == 'new') {
	        $vip = $this->getVipService()->becomeMember(
	            $order['userId'],
	            $order['data']['targetId'],
	            $order['data']['duration'], 
	            $order['data']['unitType'], 
	            $order['id']
	        );

	        $level = $this->getLevelService()->getLevel($vip['levelId']);
	        $message = "您已经成功加入 {$level['name']} ，点击查看<a href='/vip/course/level/{$level['id']}' target='_blank'>{$level['name']}</a>课程";

	    } elseif ($order['data']['buyType'] == 'renew') {
	        $vip = $this->getVipService()->renewMember(
	            $order['userId'],
	            $order['data']['duration'], 
	            $order['data']['unitType'], 
	            $order['id']
	        );

	        $level = $this->getLevelService()->getLevel($vip['levelId']);
	        $message = "您的 {$level['name']} 已成功续费，当前的有效期至：".date('Y-m-d', $vip['deadline']);

	    } elseif ($order['data']['buyType'] == 'upgrade') {
	        $vip = $this->getVipService()->upgradeMember(
	            $order['userId'],
	            $order['data']['targetId'], 
	            $order['id']
	        );

	        $level = $this->getLevelService()->getLevel($vip['levelId']);
	        $message = "您已经升级到 {$level['name']} ，点击查看<a href='/vip/course/level/{$level['id']}' target='_blank'>{$level['name']}</a>课程";
	    }

	    $this->getNotificationService()->notify($order['userId'], 'default', $message);

    }

    private function afterCouponPay($couponCode, $targetId, $amount, $priceType='RMB', $cashRate=1)
    {
        if ($priceType == 'RMB'){
            $couponResult = $this->getCouponService()->checkCouponUseable($couponCode, "vip", $targetId, $amount);
        }else{
            $couponResult = $this->getCouponService()->checkCouponUseable($couponCode, "vip", $targetId, $amount/$cashRate);
        }

        return $couponResult;
    }

    protected function getCouponService()
    {
        return ServiceKernel::instance()->createService('Coupon:Coupon.CouponService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    public function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User.NotificationService');
    }

	protected function getLevelService()
	{
		return ServiceKernel::instance()->createService("Vip:Vip.LevelService");
	}

	protected function getVipService()
    {
        return ServiceKernel::instance()->createService('Vip:Vip.VipService');
    }

    protected function getCashAccountService()
    {
        return ServiceKernel::instance()->createService('Cash.CashAccountService');
    }

    protected function getOrderService()
    {
    	return ServiceKernel::instance()->createService('Order.OrderService');
    }
}