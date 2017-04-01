<?php

namespace Biz\PayCenter\Service\Impl;

use Biz\BaseService;
use Biz\Order\OrderProcessor\OrderProcessorFactory;
use Biz\PayCenter\PayCenterException;
use Biz\PayCenter\Service\GatewayService;

class GatewayServiceImpl extends BaseService implements GatewayService
{
    public function beforePayOrder($orderId, $payment)
    {
        $order = $this->getOrderService()->getOrder($orderId);

        try {
            $this->check($order, $payment);
        } catch (PayCenterException $e) {
            $checkResult['error'] = $e->getMessage();

            return array($checkResult, null);
        }

        $newOrder = OrderProcessorFactory::create($order['targetType'])
            ->updateOrder($order['id'], array('payment' => $payment));

        $this->ifZeroOrderThenPay($newOrder);

        return array(null, $this->getOrderService()->getOrder($orderId));
    }

    private function check($order, $payment)
    {
        if (!$payment) {
            throw new PayCenterException('支付方式未开启, 请先开启', 2007);
        }

        if (!$order) {
            throw new PayCenterException('订单不存在', 2008);
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw new PayCenterException('用户未登录，不能支付。');
        }

        $paymentSetting = $this->getSettingService()->get('payment');

        if (!isset($paymentSetting['enabled']) || $paymentSetting['enabled'] == 0) {
            if (!isset($paymentSetting['disabled_message'])) {
                $paymentSetting['disabled_message'] = '尚未开启支付模块，无法购买课程。';
            }

            throw new PayCenterException($paymentSetting['disabled_message'], 2002);
        }

        $processor = OrderProcessorFactory::create($order['targetType']);
        $isTargetExist = $processor->isTargetExist($order['targetId']);

        if (!$isTargetExist) {
            throw new PayCenterException('该订单已失效', 2003);
        }

        if ($order['userId'] != $user['id']) {
            throw new PayCenterException('不是您的订单，不能支付', 2004);
        }

        if ($order['status'] != 'created') {
            throw new PayCenterException('订单状态被更改，不能支付', 2005);
        }

        if (($order['createdTime'] + 40 * 60 * 60) < time()) {
            throw new PayCenterException('订单已经过期，不能支付', 2005);
        }

        if (!empty($order['coupon'])) {
            $result = $this->getCouponService()->checkCouponUseable(
                $order['coupon'],
                $order['targetType'],
                $order['targetId'],
                $order['amount']
            );

            if ($result['useable'] == 'no') {
                throw new PayCenterException($result['message'], 2006);
            }
        }
    }

    private function ifZeroOrderThenPay($order)
    {
        if ($order['amount'] == 0 && $order['coinAmount'] == 0) {
            $payData = array(
                'sn' => $order['sn'],
                'status' => 'success',
                'amount' => $order['amount'],
                'paidTime' => time(),
            );
            $this->getPayCenterService()->processOrder($payData);
        } elseif ($order['amount'] == 0 && $order['coinAmount'] > 0) {
            $payData = array(
                'sn' => $order['sn'],
                'status' => 'success',
                'amount' => $order['amount'],
                'paidTime' => time(),
                'payment' => 'coin',
            );
            list($success, $order) = $this->getPayCenterService()->pay($payData);

            if (!$success) {
                throw new PayCenterException('非法支付订单', 2000);
            }
        }
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getCouponService()
    {
        return $this->createService('Coupon:CouponService');
    }

    protected function getPayCenterService()
    {
        return $this->createService('PayCenter:PayCenterService');
    }
}
