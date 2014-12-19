<?php

namespace Topxia\Service\PayCenter\Impl;

use Topxia\Service\PayCenter\PayCenterService;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceKernel;

class PayCenterServiceImpl extends BaseService implements PayCenterService
{
	public function pay($payData)
	{
		$connection = ServiceKernel::instance()->getConnection();
		try {
			$connection()->beginTransaction();
			
			$order = $this->getOrderService()->getOrderBySn($payData['sn']);

			$this->proccessCashFlow($order);

			list($success, $order) = $this->getOrderService()->payOrder($payData);

			$processor = OrderProcessorFactory::create($order["targetType"]);

	        if ($order['status'] == 'paid' and $processor) {
	            $router = $processor->doPaySuccess($success, $order);

	            $connection()->commit();
	            return array($success, $router, $order);
	        } else {
	        	$connection()->rollback();
	        }

		}catch (\Exception $e) {
            $connection()->rollback();
            throw $e;
        }

        return array(false, '', $order);

	}

	private function proccessCashFlow($order) {
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
		$inFlow = array(
			'userId' => $order["userId"],
            'amount' => $order["amount"],
            'name' => '入账',
            'orderSn' => $order['sn'],
            'category' => 'inFlow',
            'note' => ''
		);
		$this->getCashService()->inFlowByRmb($inFlow);

		$outFlow = array(
			'userId' => $order["userId"],
            'amount' => $order["amount"],
            'name' => $order['title'],
            'orderSn' => $order['sn'],
            'category' => 'outflow',
            'note' => ''
		);
		$this->getCashService()->outFlowByRmb($outFlow);
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

		$this->getCashService()->outFlowByCoin($cashFlow);
	}

	private function payByCoinAndMoney($order) {
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
            'note' => ''
		);

		$coinInFlow = $this->getCashService()->changeRmbToCoin($rmbOutFlow);

		$outFlow = array(
			'userId' => $userId,
            'amount' => $order["coinAmount"] + $coinInFlow["amount"],
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