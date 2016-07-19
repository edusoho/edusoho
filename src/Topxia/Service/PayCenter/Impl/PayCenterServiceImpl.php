<?php

namespace Topxia\Service\PayCenter\Impl;

use Topxia\Component\Payment\Payment;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\PayCenter\PayCenterService;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;

class PayCenterServiceImpl extends BaseService implements PayCenterService
{
    public function closeTrade($order)
    {
        if (!in_array($order['payment'], array('alipay'))) {
            return array();
        }

        $options           = $this->getPaymentOptions($order['payment']);
        $closeTradeRequest = Payment::createCloseTradeRequest($order['payment'], $options);
        $closeTradeRequest->setParams($order);
        return $closeTradeRequest->closeTrade();
    }

    public function pay($payData)
    {
        $this->getLogger('PayCenter')->info("订单号：{$payData['sn']} 进入支付处理流程");

        if ($payData['status'] != 'success') {
            $this->getLogger('PayCenter')->info("订单号：{$payData['sn']} 的订单状态为：{$payData['status']}，不能进入支付成功流程");
            return array(false, array());
        }

        $connection = ServiceKernel::instance()->getConnection();
        try {
            $connection->beginTransaction();

            $order = $this->getOrderService()->getOrderBySn($payData['sn'], true);

            if ($order["status"] == "paid") {
                $connection->rollback();
                $this->getLogger('PayCenter')->info("订单号：{$order["sn"]} 的订单状态为已支付，不能进入支付成功流程");
                return array(true, $order);
            }

            if (in_array($order["status"], array('created', 'cancelled'))) {
                $order['payment'] = $payData['payment'];
                $outflow          = $this->proccessCashFlow($order);
                $this->getLogger('PayCenter')->info("订单号：{$order["sn"]} 账单处理成功");

                if ($outflow) {
                    $this->getOrderService()->updateOrderCashSn($order["id"], $outflow["sn"]);
                    $this->getLogger('PayCenter')->info("订单号：{$order["sn"]} 更新订单的支付流水号");
                    list($success, $order) = $this->processOrder($payData, false);
                } else {
                    $order   = $this->getOrderService()->cancelOrder($order["id"], '余额不足扣款不成功');
                    $success = false;
                    $this->getLogger('PayCenter')->info("订单号：{$order["sn"]} 余额不足扣款不成功");
                }
            } else {
                $success = false;
            }

            $connection->commit();

            if ($success) {
                $this->dispatchEvent("order.pay.success",
                    new ServiceEvent($order, array('targetType' => $order["targetType"]))
                );
            }

            return array($success, $order);
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return array(false, array());
    }

    public function processOrder($payData, $lock = true)
    {
        $connection = ServiceKernel::instance()->getConnection();
        try {
            if ($lock) {
                $connection->beginTransaction();
            }
            list($success, $order) = $this->getOrderService()->payOrder($payData);
            $this->getLogger('PayCenter')->info("订单号：{$payData["sn"]} 更改订单状态为已经支付");

            if ($order["coupon"]) {
                $this->useCoupon($order);
                $this->getLogger('PayCenter')->info("订单号：{$order["sn"]} 标识优惠码为使用状态");
            }

            $processor = OrderProcessorFactory::create($order["targetType"]);

            if ($order['status'] == 'paid' && $processor) {
                $processor->doPaySuccess($success, $order);
                $this->getLogger('PayCenter')->info("订单号：{$order["sn"]} 把下单者加入学习");
            }

            if ($lock) {
                $connection->commit();
            }
            $this->getLogger('PayCenter')->info("订单号：{$order["sn"]} 订单支付处理完毕");
            return array($success, $order);
        } catch (\Exception $e) {
            if ($lock) {
                $connection->rollback();
            }

            throw $e;
        }

        return array(false, array());
    }

    protected function getPaymentOptions($payment)
    {
        $settings = $this->getSettingService()->get('payment');

        if (empty($settings)) {
            throw new \RuntimeException('支付参数尚未配置，请先配置。');
        }

        if (empty($settings['enabled'])) {
            throw new \RuntimeException("支付模块未开启，请先开启。");
        }

        if (empty($settings[$payment.'_enabled'])) {
            throw new \RuntimeException("支付模块({$payment})未开启，请先开启。");
        }

        if (empty($settings["{$payment}_key"]) || empty($settings["{$payment}_secret"])) {
            throw new \RuntimeException("支付模块({$payment})参数未设置，请先设置。");
        }

        $options = array(
            'key'    => $settings["{$payment}_key"],
            'secret' => $settings["{$payment}_secret"],
            'type'   => $settings["{$payment}_type"]
        );

        return $options;
    }

    protected function useCoupon($order)
    {
        // $couponApp = $this->getAppService()->findInstallApp("Coupon");
        // if (!empty($couponApp)) {
        $this->getCouponService()->useCoupon($order["coupon"], $order);
        // }
    }

    protected function proccessCashFlow($order)
    {
        $coinSetting = $this->getSettingService()->get("coin");

        if (!empty($coinSetting) && array_key_exists("coin_enabled", $coinSetting) && $coinSetting["coin_enabled"] == 1) {
            if ($order["amount"] == 0 && $order["coinAmount"] > 0) {
                $outflow = $this->payAllByCoin($order);
            }

            if ($order["amount"] > 0 && $order["coinAmount"] >= 0) {
                $outflow = $this->payByCoinAndRmb($order);
            }
        } else {
            $outflow = $this->payByRmb($order);
        }

        return $outflow;
    }

    protected function payByRmb($order)
    {
        $inflow = array(
            'userId'   => $order["userId"],
            'amount'   => $order["amount"],
            'name'     => '入账',
            'orderSn'  => $order['sn'],
            'category' => 'inflow',
            'note'     => '',
            'payment'  => $order['payment']
        );
        $inflow = $this->getCashService()->inflowByRmb($inflow);

        $outflow = array(
            'userId'   => $order["userId"],
            'amount'   => $order["amount"],
            'name'     => $order['title'],
            'orderSn'  => $order['sn'],
            'category' => 'outflow',
            'note'     => '',
            'parentSn' => $inflow['sn']
        );
        return $this->getCashService()->outflowByRmb($outflow);
    }

    protected function payAllByCoin($order)
    {
        $cashFlow = array(
            'userId'   => $order["userId"],
            'amount'   => $order["coinAmount"],
            'name'     => $order['title'],
            'orderSn'  => $order['sn'],
            'category' => 'outflow',
            'note'     => ''
        );

        return $this->getCashService()->outflowByCoin($cashFlow);
    }

    protected function payByCoinAndRmb($order)
    {
        $userId = $order["userId"];
        $inflow = array(
            'userId'   => $userId,
            'amount'   => $order["amount"],
            'name'     => '入账',
            'orderSn'  => $order['sn'],
            'category' => 'inflow',
            'note'     => '',
            'payment'  => $order['payment']
        );

        $rmbInFlow = $this->getCashService()->inflowByRmb($inflow);

        $rmbOutFlow = array(
            'userId'   => $userId,
            'amount'   => $order["amount"],
            'name'     => '出账',
            'orderSn'  => $order['sn'],
            'category' => 'outflow',
            'note'     => '',
            'parentSn' => $rmbInFlow['sn']
        );

        $coinInFlow = $this->getCashService()->changeRmbToCoin($rmbOutFlow);

        $totalPrice = $order["totalPrice"];

        if ($order["couponDiscount"]) {
            $totalPrice = $totalPrice - $order["couponDiscount"];
        }

        if ($order["priceType"] == "RMB") {
            $totalPrice = $totalPrice * $order['coinRate'];
        }

        $outflow = array(
            'userId'   => $userId,
            'amount'   => $totalPrice,
            'name'     => $order['title'],
            'orderSn'  => $order['sn'],
            'category' => 'outflow',
            'note'     => '',
            'parentSn' => $coinInFlow['sn']
        );

        return $this->getCashService()->outflowByCoin($outflow);
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
        return $this->createService('Coupon.CouponService');
    }
}
