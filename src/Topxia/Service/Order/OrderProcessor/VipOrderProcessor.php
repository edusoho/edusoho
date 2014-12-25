<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;

class VipOrderProcessor implements OrderProcessor
{
	protected $router = "vip";

	public function getRouter() {
		return $this->router;
	}

	public function getOrderInfo($targetId)
	{

	}

	public function shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields)
	{

	}

	public function createOrder($orderInfo) 
	{
		$orderFileds = array(
            'priceType' => $priceType,
            'totalPrice' => $totalPrice,
            'amount' => $amount,
            'coinRate' => $cashRate,
            'coinAmount' => empty($fields["coinPayAmount"])?0:$fields["coinPayAmount"],
            'userId' => $user["id"],
            'payment' => 'alipay',
            'targetId' => $targetId,
            'coupon' => empty($couponResult) ? null : $fields["couponCode"],
            'couponDiscount' => empty($couponDiscount) ? null : $couponDiscount,
        );

		$order = array(

		);
        $unitNames = array('month' => '个月', 'year' => '年');

        $order['title'] = ($orderData['type'] == 'renew' ? '续费' : '购买') .  "{$level['name']} x {$orderData['duration']}{$unitNames[$orderData['unit']]}";
        $order['targetType'] = 'vip';
        $order['payment'] = 'alipay';
        $order['amount'] = $level[$orderData['unit'] . 'Price'] * $orderData['duration'];
        $order['snPrefix'] = 'V';
        $order['data'] = $orderData;

		return $this->getOrderService()->createOrder($order);
	}

	public function updateOrder($orderInfo) 
	{
		return $this->getCourseOrderService()->updateOrder($orderId, $orderInfo);
	}

	public function doPaySuccess($success, $order) {
        if (!$success) {
            return ;
        }

        $this->getCourseOrderService()->doSuccessPayOrder($order['id']);

        return ;
    }

	protected function getLevelService() {
		return ServiceKernel::instance()->createService("Vip:Vip.LevelService");
	}
}