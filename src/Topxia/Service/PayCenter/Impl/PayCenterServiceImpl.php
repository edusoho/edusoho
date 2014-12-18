<?php

namespace Topxia\Service\PayCenter\Impl;

use Topxia\Service\PayCenter\PayCenterService;
use Topxia\Service\Common\BaseService;

class PayCenterServiceImpl extends BaseService implements PayCenterService
{
	public function pay($payData)
	{
		$order = $this->getOrderService()->getOrderBySn($payData['sn']);

		if($order["priceType"] == "Coin")
			if($order["amount"] == 0 && $order["coinAmount"] > 0) {
				$this->payAllByCoin($order);
			}
			if($order["amount"] > 0 && $order["coinAmount"] >= 0) {
				$this->payByCoinAndMoney($order);
			}
		} else if($order["priceType"] == "RMB") {
			$this->payByMoney($order);
		}

	}

	private function payByMoney($order) {

		$this->getCashService()->inFlowByRmb($userId, $inFlow);
		$this->getCashService()->outFlowByRmb($userId, $inFlow);
	}

	private function payAllByCoin($order) {
		
		$userId = $order["userId"];
		$cashFlow = array(
			'userId' => $userId,
            'type' => 'outflow',
            'amount' => $order["coinAmount"],
            'name' => $order['title'],
            'orderSn' => $order['sn'],
            'category' => 'outflow',
            'note' => ''
		);

		$this->getCashService()->outFlowByCoin($userId, $cashFlow);

		//扣除金额 
	}

	private function payByCoinAndMoney($order) {
		$userId = $order["userId"];
		$inFlow = array(
			'userId' => $userId,
            'type' => 'outflow',
            'amount' => $order["coinAmount"],
            'name' => $order['title'],
            'orderSn' => $order['sn'],
            'category' => 'outflow',
            'note' => ''
		);

		$this->getCashService()->inFlowByRmb($userId, $inFlow);
		$coin = $this->getCashService()->changeRmbToCoin($userId, $cashFlow);

		

		$outFlow = array(
			'userId' => $userId,
            'type' => 'outflow',
            'amount' => $order["coinAmount"] + $coin,
            'name' => $order['title'],
            'orderSn' => $order['sn'],
            'category' => 'outflow',
            'note' => ''
		);

		$this->getCashService()->outFlowByCoin($userId, $outFlow);
	}

	protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getCashService()
    {
        return $this->getServiceKernel()->createService('Cash.CashService');
    }
}