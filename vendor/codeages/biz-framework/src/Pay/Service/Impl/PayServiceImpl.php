<?php

namespace Codeages\Biz\Framework\Pay\Service\Impl;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Targetlog\Service\TargetlogService;

class PayServiceImpl extends BaseService implements PayService
{
    public function createTrade($data)
    {
        $data = ArrayToolkit::parts($data, array(
            'goods_title',
            'goods_detail',
            'attach',
            'order_sn',
            'amount',
            'coin_amount',
            'notify_url',
            'create_ip',
            'pay_type',
            'platform',
            'open_id',
            'device_info',
            'seller_id',
            'user_id'
        ));

        $lock = $this->biz['lock'];

        try {
            $lock->get("trade_create_{$data['order_sn']}");
            $this->beginTransaction();

            $trade = $this->createPaymentTrade($data);

            if ($trade['cash_amount'] != 0) {
                $result = $this->createPaymentPlatformTrade($data, $trade);
                $trade = $this->getPaymentTradeDao()->update($trade['id'], array(
                    'platform_created_result' => $result
                ));
            } else {
                $mockNotify = array (
                    'status' => 'paid',
                    'paid_time' => time(),
                    'cash_flow' => '',
                    'cash_type' => '',
                    'trade_sn' => $trade['trade_sn'],
                    'pay_amount' => '0',
                );

                $this->proccessNotify($mockNotify);
            }

            $this->commit();
            $lock->release("trade_create_{$data['order_sn']}");
        } catch (\Exception $e) {
            $this->rollback();
            $lock->release("trade_create_{$data['order_sn']}");
            throw $e;
        }
        return $trade;
    }

    public function getTradeByTradeSn($tradeSn)
    {
        return $this->getPaymentTradeDao()->getByTradeSn($tradeSn);
    }

    public function queryTradeFromPlatform($tradeSn)
    {
        $trade = $this->getPaymentTradeDao()->getByTradeSn($tradeSn);
        return $this->getPayment($trade['platform'])->queryTrade($trade);
    }

    public function closeTradesByOrderSn($orderSn)
    {
        $trades = $this->getPaymentTradeDao()->findByOrderSn($orderSn);
        if (empty($trades)) {
            return;
        }

        foreach ($trades as $trade) {
            $this->getTradeContext($trade['id'])->closing();
        }
    }

    public function notifyPaid($payment, $data)
    {
        list($data, $result) = $this->getPayment($payment)->converterNotify($data);
        $this->getTargetlogService()->log(TargetlogService::INFO, 'pay.notify_received', $data['trade_sn'], "收到第三方支付平台{$payment}的通知，交易号{$data['trade_sn']}，支付状态{$data['status']}", $data);

        $this->proccessNotify($data);
        return $result;
    }

    protected function proccessNotify($data)
    {
        if ($data['status'] == 'paid') {
            $lock = $this->biz['lock'];
            try {
                $lock->get("pay_notify_{$data['trade_sn']}");

                $trade = $this->getPaymentTradeDao()->getByTradeSn($data['trade_sn']);
                if (empty($trade)) {
                    $this->getTargetlogService()->log(TargetlogService::INFO, 'pay.trade_empty', $data['trade_sn'], "交易号{$data['trade_sn']}不存在", $data);
                    $lock->release("pay_notify_{$data['trade_sn']}");
                    return;
                }

                $cashFlows = $this->findUserCashflowsByTradeSn($trade['trade_sn']);
                if (!empty($cashFlows)) {
                    $this->getTargetlogService()->log(TargetlogService::INFO, 'pay.notify_exist', $data['trade_sn'], "交易号{$data['trade_sn']}，已存在流水，不处理此通知", $data);
                    $lock->release("pay_notify_{$data['trade_sn']}");
                    return;
                }

                $trade = $this->updateTrade($trade, $data);

                $lock->release("pay_notify_{$data['trade_sn']}");

            } catch (\Exception $e) {
                $this->rollback();
                $lock->release("pay_notify_{$data['trade_sn']}");
                $this->getTargetlogService()->log(TargetlogService::INFO, 'pay.error', $data['trade_sn'], "交易号{$data['trade_sn']}处理失败, {$e->getMessage()}", $data);
                throw $e;
            }

            $this->dispatch('pay.success', $trade, $data);
        }
    }

    protected function updateTrade($trade, $data)
    {
        try {
            $this->beginTransaction();
            $trade = $this->getPaymentTradeDao()->update($trade['id'], array(
                'status' => $data['status'],
                'pay_time' => $data['paid_time'],
                'platform_sn' => $data['cash_flow'],
                'notify_data' => $data,
                'currency' => $data['cash_type'],
            ));
            $this->createCashFlow($trade, $data);
            $this->getTargetlogService()->log(TargetlogService::INFO, 'pay.success', $data['trade_sn'], "交易号{$data['trade_sn']}，账目流水处理成功", $data);
            $this->commit();
            return $trade;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function findEnabledPayments()
    {
        return $this->biz['payment.platforms'];
    }

    public function notifyClosed($data)
    {
        $trade = $this->getPaymentTradeDao()->getByTradeSn($data['sn']);
        return $this->getTradeContext($trade['id'])->closed();
    }

    public function applyRefundByTradeSn($tradeSn)
    {
        $trade = $this->getPaymentTradeDao()->getByTradeSn($tradeSn);

        if (in_array($trade['status'], array('refunding', 'refunded'))) {
            return $trade;
        }

        if ($trade['status'] != 'paid') {
            throw new AccessDeniedException('can not refund, becourse the trade is not paid');
        }

        if ((time() - $trade['pay_time']) > 86400) {
            throw new AccessDeniedException('can not refund, becourse the paid trade is expired.');
        }

        $paymentGetWay = $this->getPayment($trade['platform']);
        $response = $paymentGetWay->applyRefund($trade);

        if (!$response->isSuccessful()) {
            return $trade;
        }

        $trade = $this->getPaymentTradeDao()->update($trade['id'], array(
            'status' => 'refunding',
            'apply_refund_time' => time()
        ));
        $this->dispatch('trade.refunding', $trade);

        // TODO: 当支付宝时，直接修改状态为refunded

        return $trade;
    }

    public function notifyRefunded($payment, $data)
    {
        $paymentGetWay = $this->getPayment($payment);
        $response = $paymentGetWay->converterRefundNotify($data);
        $tradeSn = $response[0]['notify_data']['trade_sn'];

        $trade = $this->getPaymentTradeDao()->getByTradeSn($tradeSn);

        return $this->getTradeContext($trade['id'])->refunded();
    }

    protected function validateLogin()
    {
        if (empty($this->biz['user']['id'])) {
            throw new AccessDeniedException('user is not login.');
        }
    }

    protected function createPaymentTrade($data)
    {
        $rate = $this->getCoinRate();

        $trade = array(
            'title' => $data['goods_title'],
            'trade_sn' => $this->generateSn(),
            'order_sn' => $data['order_sn'],
            'platform' => $data['platform'],
            'price_type' => $this->getCurrencyType(),
            'amount' => $data['amount'],
            'rate' => $this->getCoinRate(),
            'seller_id' => empty($data['seller_id']) ? 0 : $data['seller_id'],
            'user_id' => $this->biz['user']['id'],
        );

        if (empty($data['coin_amount'])) {
            $trade['coin_amount'] = 0;
        } else {
            $trade['coin_amount'] = $data['coin_amount'];
        }

        if ('money' == $trade['price_type']) {
            $trade['cash_amount'] = ceil(($trade['amount'] * $trade['rate'] - $trade['coin_amount']) / $trade['rate'] ); // 标价为人民币，可用虚拟币抵扣
        } else {
            $trade['cash_amount'] = ceil(($trade['amount'] - $trade['coin_amount']) / $rate); // 标价为虚拟币
        }

        $savedTrade = $this->getPaymentTradeDao()->getByOrderSnAndPlatform($data['order_sn'], $data['platform']);
        if (empty($savedTrade)) {
            return $this->getPaymentTradeDao()->create($trade);
        } else {
            return $this->getPaymentTradeDao()->update($savedTrade['id'], $trade);
        }
    }

    protected function findUserCashflowsByTradeSn($sn)
    {
        return $this->getUserCashflowDao()->findByTradeSn($sn);
    }

    protected function createCashFlow($trade, $notifyData)
    {
        if ('refund' == $trade['type']) {
            $this->createSiteFlow($trade, array(), 'outflow');
            return;
        }
        $inflow = $this->createUserFlow($trade, array('amount' => $notifyData['pay_amount']), 'inflow');
        $outflow = $this->createUserFlow($trade, $inflow, 'outflow');
        $this->createSiteFlow($trade, $outflow, 'inflow');

        if ('recharge' == $trade['type']) {
            $outflow = $this->createSiteFlow($trade, $outflow, 'outflow', true);
            $this->createUserFlow($trade, $outflow, 'inflow', true);
        } elseif ('purchase' == $trade['type']) {
            if (!empty($trade['coin_amount'])) {
                $outflow = $this->createUserFlow($trade, $outflow, 'outflow', true);
                $this->createSiteFlow($trade, $outflow, 'inflow', true);
            }
        }
    }

    protected function createSiteFlow($trade, $flow = array(), $flowType, $isCoin = false)
    {
        $siteFlow = array(
            'sn' => $this->generateSn(),
            'title' => $trade['title'],
            'trade_sn' => $trade['trade_sn'],
            'order_sn' => $trade['order_sn'],
            'platform_sn' => $trade['platform_sn'],
            'platform' => $trade['platform'],
            'price_type' => $trade['price_type'],
            'currency' => $isCoin ? 'coin': $trade['currency'],
            'amount' => empty($flow) ? 0 : ($isCoin && $flowType == 'outflow' ? $flow['amount'] * $this->getCoinRate() : $flow['amount']),
            'pay_time' => $trade['pay_time'],
            'user_cashflow' => empty($flow['sn']) ? '' : $flow['sn'],
            'type' => $flowType,
            'seller_id' => $trade['seller_id']
        );

        if ($siteFlow['amount'] == 0) {
            return array();
        }

        return $this->getSiteCashFlowDao()->create($siteFlow);
    }

    protected function createUserFlow($trade, $parentFlow, $flowType, $isCoin = false)
    {
        $userFlow = array(
            'sn' => $this->generateSn(),
            'type' => $flowType,
            'parent_sn' => empty($parentFlow['sn']) ? '' : $parentFlow['sn'],
            'currency' => $isCoin ? 'coin': $trade['currency'],
            'user_id' => $trade['user_id'],
            'trade_sn' => $trade['trade_sn'],
            'order_sn' => $trade['order_sn'],
            'platform' => $trade['platform'],
        );

        if ($isCoin && $flowType == 'inflow') {
            $userFlow['amount'] = $trade['cash_amount'] * $this->getCoinRate();
        } else if ($isCoin && $flowType == 'outflow') {
            $userFlow['amount'] = $trade['coin_amount'] + $trade['cash_amount'] * $this->getCoinRate();
        } else {
            $userFlow['amount'] = $trade['cash_amount'];
        }

        if ($userFlow['amount'] == 0) {
            return array();
        }

        $userFlow = $this->getUserCashflowDao()->create($userFlow);
        $amount = $flowType == 'inflow' ? $userFlow['amount'] : 0 - $userFlow['amount'];
        if ($isCoin) {
            $this->getAccountService()->waveCashAmount($userFlow['user_id'], $amount);
        } else {
            $this->getAccountService()->waveAmount($userFlow['user_id'], $amount);
        }
        return $userFlow;
    }

    protected function getSiteCashFlowDao()
    {
        return $this->biz->dao('Pay:SiteCashflowDao');
    }

    protected function generateSn($prefix = '')
    {
        return $prefix.date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function getUserCashflowDao()
    {
        return $this->biz->dao('Pay:UserCashflowDao');
    }

    protected function getTargetlogService()
    {
        return $this->biz->service('Targetlog:TargetlogService');
    }

    protected function getPaymentTradeDao()
    {
        return $this->biz->dao('Pay:PaymentTradeDao');
    }

    protected function getAccountService()
    {
        return $this->biz->service('Pay:AccountService');
    }

    protected function getCoinRate()
    {
        return 1;
    }

    protected function getCurrencyType()
    {
        return 'money';
    }

    protected function getPayment($payment)
    {
        return $this->biz["payment.{$payment}"];
    }

    protected function createPaymentPlatformTrade($data, $trade)
    {
        $data['trade_sn'] = $trade['trade_sn'];
        unset($data['user_id']);
        unset($data['seller_id']);
        return $this->getPayment($data['platform'])->createTrade($data);
    }

    protected function getTradeContext($id)
    {
        $tradeContext = $this->biz['payment_trade_context'];

        $trade = $this->getPaymentTradeDao()->get($id);
        if (empty($trade)) {
            throw $this->createNotFoundException("trade #{$trade['id']} is not found");
        }

        $tradeContext->setPaymentTrade($trade);

        return $tradeContext;
    }
}