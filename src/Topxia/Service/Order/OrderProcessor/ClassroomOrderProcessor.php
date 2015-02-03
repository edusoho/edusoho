<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\NumberToolkit;
use Exception;

class ClassroomOrderProcessor extends BaseProcessor implements OrderProcessor
{
	protected $router = "classroom_show";

	public function getRouter() {
		return $this->router;
	}

	public function getOrderInfo($targetId, $fields)
	{
        $classroom = $this->getClassroomService()->getClassroom($targetId);
        
        if(empty($classroom)) {
            throw new Exception("找不到要购买的班级!");
        }

        $totalPrice = 0;

        $coinSetting = $this->getSettingService()->get("coin");

        if(!isset($coinSetting["coin_enabled"]) 
            || !$coinSetting["coin_enabled"]) {
        	$totalPrice = $classroom["price"];
        	return array(
				'totalPrice' => $totalPrice,
				'targetId' => $targetId,
            	'targetType' => "classroom",

				'classroom' => empty($classroom) ? null : $classroom,
        	);
        }

        $cashRate = 1;
        if(array_key_exists("cash_rate", $coinSetting)) {
            $cashRate = $coinSetting["cash_rate"];
        }

        $priceType = "RMB";
        if(array_key_exists("price_type", $coinSetting)) {
            $priceType = $coinSetting["price_type"];
        }

        if($priceType == "Coin"){
            $totalPrice = $classroom["price"] * $cashRate;
        } else {
            $totalPrice = $classroom["price"];
        }

        $user = $this->getUserService()->getCurrentUser();
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

        $coinPayAmount = NumberToolkit::roundUp($coinPayAmount);
        return array(
            'classroom' => empty($classroom) ? null : $classroom,
            
            'totalPrice' => $totalPrice,
            'targetId' => $targetId,
            'targetType' => "classroom",
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
        $classroom = $this->getClassroomService()->getClassroom($targetId);

        if($priceType == "RMB") {
            $totalPrice = $classroom["price"];
        } else if ($priceType == "Coin") {
            $totalPrice = $classroom["price"] * $cashRate;
        }

        if($totalPrice != $fields['totalPrice']) {
            throw new Exception("实际价格不匹配，不能创建订单!");
        }

        $totalPrice = (float)$totalPrice;
        $amount = $totalPrice;

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
		return $this->getClassroomOrderService()->createOrder($orderInfo);
	}

	public function doPaySuccess($success, $order) {
        if (!$success) {
            return ;
        }

        $this->getClassroomOrderService()->doSuccessPayOrder($order['id']);

        return ;
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom.ClassroomService');
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

	protected function getClassroomOrderService() {
		return ServiceKernel::instance()->createService("Classroom.ClassroomOrderService");
	}
}