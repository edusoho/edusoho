<?php

namespace Biz\PayCenter\Service\Impl;

use Biz\BaseService;
use Biz\Order\OrderProcessor\OrderProcessorFactory;
use Biz\PayCenter\PayCenterException;
use Biz\PayCenter\Service\GatewayService;

class GatewayServiceImpl extends BaseService implements GatewayService
{
    public function beforePayOrder($orderId, $targetType, $payment)
    {
        $processor = OrderProcessorFactory::create($targetType);
        $order = $processor->updateOrder($orderId, array('payment' => $payment));

        try {
            $this->check($processor, $order, $payment);
        } catch (PayCenterException $e) {
            $checkResult['error'] = $e->getMessage();
            $checkResult['code'] = $e->getCode();

            return array($checkResult, null);
        }

        $newOrder = $this->ifZeroOrderThenPay($order);

        return array(null, $newOrder);
    }

    private function check($processor, $order, $payment)
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

        if (!empty($order['targetId'])) {
            $isTargetExist = $processor->isTargetExist($order['targetId']);

            if (!$isTargetExist) {
                throw new PayCenterException('该订单已失效', 2003);
            }
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
            list($success, $newOrder) = $this->getPayCenterService()->processOrder($payData);

            if (!$success) {
                throw new PayCenterException('非法支付订单', 2000);
            }

            return $newOrder;
        } elseif ($order['amount'] == 0 && $order['coinAmount'] > 0) {
            $payData = array(
                'sn' => $order['sn'],
                'status' => 'success',
                'amount' => $order['amount'],
                'paidTime' => time(),
                'payment' => 'coin',
            );
            list($success, $newOrder) = $this->getPayCenterService()->pay($payData);

            if (!$success) {
                throw new PayCenterException('非法支付订单', 2000);
            }

            return $newOrder;
        }

        return $order;
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
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
