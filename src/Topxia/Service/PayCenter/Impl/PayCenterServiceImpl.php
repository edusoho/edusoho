<?php

namespace Topxia\Service\PayCenter\Impl;

use Topxia\Service\PayCenter\PayCenterService;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;

class PayCenterServiceImpl extends BaseService implements PayCenterService
{
	public function pay($payData)
	{
		$connection = ServiceKernel::instance()->getConnection();
		try {
			$connection->beginTransaction();
			
			$order = $this->getOrderService()->getOrderBySn($payData['sn'],true);

			if($order["status"] == "paid"){
				$connection->rollback();
				$processor = OrderProcessorFactory::create($order["targetType"]);
				return array(true, $processor->getRouter(), $order);
			}

			$this->proccessCashFlow($order);

			list($success, $router, $order) = $this->processOrder($payData);
	        
            $connection->commit();
            return array($success, $router, $order);
		} catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return array(false, '', array());

	}

	public function processOrder($payData)
	{
		$connection = ServiceKernel::instance()->getConnection();
		try {
			$connection->beginTransaction();

			list($success, $order) = $this->getOrderService()->payOrder($payData);

			if($order["coupon"]) {
				$couponApp = $this->getAppService()->findInstallApp("Coupon");
				if(!empty($couponApp)) {
					$this->getCouponService()->useCoupon($order["coupon"], $order);
				}
			}

			$processor = OrderProcessorFactory::create($order["targetType"]);

			$router = '';
	        if ($order['status'] == 'paid' and $processor) {
	            $router = $processor->doPaySuccess($success, $order);
	        }

	        $connection->commit();
	        return array($success, $processor->getRouter(), $order);
        }catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
        
        return array(false, '', array());
	}

	private function proccessCashFlow($order) {
		$coinSetting = $this->getSettingService()->get("coin");

		if(!empty($coinSetting) && array_key_exists("coin_enabled", $coinSetting) && $coinSetting["coin_enabled"] == 1) {
			if($order["amount"] == 0 && $order["coinAmount"] > 0) {
				$outFlow = $this->payAllByCoin($order);
			}
			if($order["amount"] > 0 && $order["coinAmount"] >= 0) {
				$outFlow = $this->payByCoinAndRmb($order);
			}
		} else {
			$outFlow = $this->payByRmb($order);
		}

		$this->getOrderService()->updateOrderCashSn($order["id"], $outFlow["sn"]);

		return $outFlow;
	}

	private function payByRmb($order) {
		$inFlow = array(
			'userId' => $order["userId"],
            'amount' => $order["amount"],
            'name' => '入账',
            'orderSn' => $order['sn'],
            'category' => 'inFlow',
            'note' => ''
		);
		$inFlow = $this->getCashService()->inFlowByRmb($inFlow);

		$outFlow = array(
			'userId' => $order["userId"],
            'amount' => $order["amount"],
            'name' => $order['title'],
            'orderSn' => $order['sn'],
            'category' => 'outflow',
            'note' => '',
            'parentSn' => $inFlow['sn']
		);
		return $this->getCashService()->outFlowByRmb($outFlow);
	}

	private function payAllByCoin($order) {
		
		$cashFlow = array(
			'userId' => $order["userId"],
            'amount' => $order["coinAmount"],
            'name' => $order['title'],
            'orderSn' => $order['sn'],
            'category' => 'outflow',
            'note' => ''
		);

		return $this->getCashService()->outFlowByCoin($cashFlow);
	}

	private function payByCoinAndRmb($order) {
		$userId = $order["userId"];
		$inFlow = array(
			'userId' => $userId,
            'amount' => $order["amount"],
            'name' => '入账',
            'orderSn' => $order['sn'],
            'category' => 'outflow',
            'note' => ''
		);

		$rmbInFlow = $this->getCashService()->inFlowByRmb($inFlow);

		$rmbOutFlow = array(
			'userId' => $userId,
            'amount' => $order["amount"],
            'name' => '出账',
            'orderSn' => $order['sn'],
            'category' => 'inflow',
            'note' => '',
            'parentSn' => $rmbInFlow['sn']
		);

		$coinInFlow = $this->getCashService()->changeRmbToCoin($rmbOutFlow);

		$outFlow = array(
			'userId' => $userId,
            'amount' => $order["coinAmount"] + $coinInFlow["amount"],
            'name' => $order['title'],
            'orderSn' => $order['sn'],
            'category' => 'outflow',
            'note' => '',
            'parentSn' => $coinInFlow['sn']
		);

		return $this->getCashService()->outFlowByCoin($outFlow);
	}

	protected function getAppService()
    {
        return $this->createService('CloudPlatform.AppService');
    }

	protected function getOrderService()
    {
        return $this->createService('Order.OrderService');
    }

    protected function getCashService()
    {
        return $this->createService('Cash.CashService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getCouponService()
    {
        return $this->createService('Coupon:Coupon.CouponService');
    }
}