<?php

namespace Codeages\Biz\Pay\Service\Impl;

use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Pay\Status\PaidStatus;
use Codeages\Biz\Pay\Status\PayingStatus;
use Codeages\Biz\Pay\Status\RefundedStatus;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Pay\Service\PayService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Targetlog\Service\TargetlogService;

class PayServiceImpl extends BaseService implements PayService
{
    public function createTrade($data, $createPlatformTrade = true)
    {
        $data = ArrayToolkit::parts($data, array(
            'goods_title',
            'goods_detail',
            'attach',
            'order_sn',
            'amount',
            'coin_amount',
            'notify_url',
            'return_url',
            'show_url',
            'create_ip',
            'platform_type',
            'platform',
            'open_id',
            'device_info',
            'seller_id',
            'user_id',
            'type',
            'rate',
            'app_pay',
        ));

        if ('recharge' == $data['type']) {
            return $this->createRechargeTrade($data, $createPlatformTrade);
        } elseif ('purchase' == $data['type']) {
            return $this->createPurchaseTrade($data, $createPlatformTrade);
        } else {
            throw new InvalidArgumentException("can't create the type of {$data['type']} trade");
        }
    }

    protected function createPurchaseTrade($data, $createPlatformTrade)
    {
        try {
            $this->beginTransaction();

            $trade = $this->createPayTrade($data);

            if ($trade['coin_amount'] > 0) {
                $user = $this->biz['user'];
                $this->getAccountService()->lockCoin($user['id'], $trade['coin_amount']);
            }

            if ($trade['cash_amount'] > 0 && $createPlatformTrade) {
                $trade = $this->createPaymentPlatformTrade($data, $trade);
            }

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $trade;
    }

    protected function createRechargeTrade($data, $createPlatformTrade)
    {
        try {
            $this->beginTransaction();
            $trade = $this->createPayTrade($data);

            if ($createPlatformTrade) {
                $trade = $this->createPaymentPlatformTrade($data, $trade);
            }

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $trade;
    }

    public function getTradeById($id)
    {
        return $this->getPayTradeDao()->getById($id);
    }

    public function getTradeByTradeSn($tradeSn)
    {
        return $this->getPayTradeDao()->getByTradeSn($tradeSn);
    }

    public function findTradesByTradeSn($tradeSns)
    {
        return $this->getPayTradeDao()->findByTradeSns($tradeSns);
    }

    public function queryTradeFromPlatform($tradeSn)
    {
        $trade = $this->getPayTradeDao()->getByTradeSn($tradeSn);
        $result = $this->getPayment($trade['platform'])->queryTrade($tradeSn);

        if (PaidStatus::NAME != $trade['status'] && !empty($result)) {
            $paidTrade = $this->updateTradeToPaidAndTransferAmount($result);
            if ($paidTrade) {
                $paidTrade['platform_trade'] = $result;

                return $paidTrade;
            }
        }

        $trade['platform_trade'] = $result;

        return $trade;
    }

    public function findTradesByIds($ids)
    {
        return $this->getPayTradeDao()->findByIds($ids);
    }

    public function findTradesByOrderSns($orderSns)
    {
        return $this->getPayTradeDao()->findByOrderSns($orderSns);
    }

    public function setTradeInvoiceSnById($id, $invoiceSn)
    {
        $trade = $this->getTradeById($id);
        if (empty($trade)) {
            throw new NotFoundException('trade not found');
        }

        if (!empty($trade['invoice_sn'])) {
            $invoice = $this->getInvoiceService()->getInvoiceBySn($trade['invoice_sn']);
            if ('refused' != $invoice['status']) {
                throw new AccessDeniedException('trade had invoice');
            }
        }

        if ($this->biz['user']['id'] != $trade['user_id']) {
            throw new AccessDeniedException('trade owner is invalid.');
        }

        return $this->getPayTradeDao()->update($trade['id'], array('invoice_sn' => $invoiceSn));
    }

    public function closeTradesByOrderSn($orderSn, $excludeTradeSns = array())
    {
        $trades = $this->getPayTradeDao()->findByOrderSn($orderSn);
        if (empty($trades)) {
            return;
        }

        foreach ($trades as $trade) {
            if (in_array($trade['trade_sn'], $excludeTradeSns)) {
                continue;
            }

            $trade = $this->getTradeContext($trade['id'])->closing();
            if ($this->isCloseByPayment()) {
                $this->closeByPayment($trade);
            }

            $data = array('sn' => $trade['trade_sn']);
            $this->notifyClosed($data);
        }
    }

    public function notifyPaid($payment, $data)
    {
        if ('coin' == $payment) {
            $mockNotify = array(
                'status' => 'paid',
                'paid_time' => time(),
                'cash_flow' => '',
                'cash_type' => '',
                'trade_sn' => $data['trade_sn'],
                'pay_amount' => '0',
            );

            $trade = $this->updateTradeToPaidAndTransferAmount($mockNotify);

            return $trade;
        }

        list($data, $result) = $this->getPayment($payment)->converterNotify($data);
        $this->getTargetlogService()->log(TargetlogService::INFO, 'trade.paid_notify', $data['trade_sn'], "收到第三方支付平台{$payment}的通知，交易号{$data['trade_sn']}，支付状态{$data['status']}", $data);

        $this->updateTradeToPaidAndTransferAmount($data);

        return $result;
    }

    public function rechargeByIap($data)
    {
        list($data, $result) = $this->getPayment('iap')->converterNotify($data);

        if ('failure' == $result) {
            throw new \Exception($data['msg']);
        }

        $platformSn = $data['cash_flow'];
        $lockKey = "recharge_by_iap_{$platformSn}";
        $lock = $this->biz['lock'];
        $lock->get($lockKey);

        $trade = $this->getTradeByPlatformSn($platformSn);

        if (!empty($trade) && 'iap' == $trade['platform']) {
            return $trade;
        }

        $trade = array(
            'goods_title' => '充值',
            'order_sn' => '',
            'platform' => 'iap',
            'platform_type' => '',
            'amount' => $data['pay_amount'],
            'user_id' => $data['attach']['user_id'],
            'type' => 'recharge',
        );
        $trade = $this->createPayTrade($trade);

        $data = array(
            'paid_time' => strtotime($data['paid_time']),
            'cash_flow' => $data['cash_flow'],
            'cash_type' => 'CNY',
            'trade_sn' => $trade['trade_sn'],
            'status' => 'paid',
            'pay_amount' => $data['pay_amount'],
        );
        $this->updateTradeToPaidAndTransferAmount($data);
        $trade = $this->getPayTradeDao()->get($trade['id']);

        $lock->release($lockKey);

        return $trade;
    }

    protected function isCloseByPayment()
    {
        return empty($this->biz['payment.final_options']['closed_by_notify']) ? false : $this->biz['payment.final_options']['closed_by_notify'];
    }

    protected function closeByPayment($data)
    {
        try {
            $response = $this->getPayment($data['platform'])->closeTrade($data);
            if (!empty($response) && !$response->isSuccessful()) {
                if (method_exists($response, 'getFailData')) {
                    $failData = $response->getFailData();
                } else {
                    $failData = $response->getMessage();
                }
                $this->getTargetlogService()->log(TargetlogService::INFO, 'trade.close_failed', $data['trade_sn'], "交易号{$data['trade_sn']}关闭失败,{$failData},(order_sn:{$data['order_sn']})", $data);
            } else {
                $this->getTargetlogService()->log(TargetlogService::INFO, 'trade.close', $data['trade_sn'], "交易号{$data['trade_sn']}关闭成功。(order_sn:{$data['order_sn']})", $data);
            }

            return $response;
        } catch (\Exception $e) {
            $this->getTargetlogService()->log(TargetlogService::INFO, 'trade.close_failed', $data['trade_sn'], "交易号{$data['trade_sn']}关闭失败,{$e->getMessage()},(order_sn:{$data['order_sn']})", $data);
            return null;
        }
    }

    protected function updateTradeToPaidAndTransferAmount($data)
    {
        if ('paid' == $data['status']) {
            $trade = $this->getPayTradeDao()->getByTradeSn($data['trade_sn']);
            if (empty($trade)) {
                $this->getTargetlogService()->log(TargetlogService::INFO, 'trade.not_found', $data['trade_sn'], "交易号{$data['trade_sn']}不存在", $data);

                return $trade;
            }

            try {
                $this->beginTransaction();

                $trade = $this->getPayTradeDao()->get($trade['id'], array('lock' => true));

                if (PayingStatus::NAME != $trade['status']) {
                    $this->getTargetlogService()->log(TargetlogService::INFO, 'trade.is_not_paying', $data['trade_sn'], "交易号{$data['trade_sn']}状态不正确，状态为：{$trade['status']}", $data);
                    $this->commit();

                    return $trade;
                }

                if ($trade['cash_amount'] != $data['pay_amount']) {
                    $this->getTargetlogService()->log(TargetlogService::INFO, 'trade.pay_amount.mismatch', $data['trade_sn'], "{$data['trade_sn']}实际支付的价格{$data['pay_amount']}和交易记录价格{$trade['cash_amount']}不匹配，状态为：{$trade['status']}", $data);
                }

                $trade = $this->updateTradeToPaid($trade['id'], $data);
                $this->transfer($trade);
                if ('purchase' == $trade['type']) {
                    $this->closeTradesByOrderSn($trade['order_sn'], array($trade['trade_sn']));
                }
                $this->getTargetlogService()->log(TargetlogService::INFO, 'trade.paid', $data['trade_sn'], "交易号{$data['trade_sn']}，账目流水处理成功", $data);

                $this->commit();
            } catch (\Exception $e) {
                $this->rollback();
                $this->getTargetlogService()->log(TargetlogService::INFO, 'pay.error', $data['trade_sn'], "交易号{$data['trade_sn']}处理失败, {$e->getMessage()}", $data);
                throw $e;
            }

            $this->dispatch('payment_trade.paid', $trade, $data);

            return $trade;
        }

        return $this->getPayTradeDao()->getByTradeSn($data['trade_sn']);
    }

    public function searchTrades($conditions, $orderBy, $start, $limit, $columns = array())
    {
        return $this->getPayTradeDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function countTrades($conditions)
    {
        return $this->getPayTradeDao()->count($conditions);
    }

    protected function updateTradeToPaid($tradeId, $data)
    {
        $updatedFields = array(
            'status' => $data['status'],
            'pay_time' => $data['paid_time'],
            'platform_sn' => $data['cash_flow'],
            'notify_data' => $data,
            'currency' => $data['cash_type'],
        );

        return $this->getPayTradeDao()->update($tradeId, $updatedFields);
    }

    public function findEnabledPayments()
    {
        return $this->biz['payment.platforms'];
    }

    public function notifyClosed($data)
    {
        $trade = $this->getPayTradeDao()->getByTradeSn($data['sn']);

        return $this->getTradeContext($trade['id'])->closed();
    }

    public function applyRefundByTradeSn($tradeSn, $data = array())
    {
        $trade = $this->getPayTradeDao()->getByTradeSn($tradeSn);
        if (in_array($trade['status'], array('refunding', 'refunded'))) {
            return $trade;
        }

        if ('paid' != $trade['status']) {
            throw new AccessDeniedException('can not refund, because the trade is not paid');
        }

        if ($this->isRefundByPayment()) {
            return $this->refundPlatformTrade($trade);
        }

        $trade = $this->updateTradeToRefunded($tradeSn, $data);

        return $trade;
    }

    protected function isRefundByPayment()
    {
        return empty($this->biz['payment.final_options']['refunded_by_notify']) ? false : $this->biz['payment.final_options']['refunded_by_notify'];
    }

    protected function refundPlatformTrade($trade)
    {
        $paymentGateway = $this->getPayment($trade['platform']);
        $response = $paymentGateway->applyRefund($trade);

        if (!$response->isSuccessful()) {
            return $trade;
        }

        $trade = $this->getPayTradeDao()->update($trade['id'], array(
            'status' => 'refunding',
            'apply_refund_time' => time(),
        ));
        $this->dispatch('payment_trade.refunding', $trade);

        return $trade;
    }

    public function notifyRefunded($payment, $data)
    {
        $paymentGateway = $this->getPayment($payment);
        list($result, $response) = $paymentGateway->converterRefundNotify($data);
        $tradeSn = $result['trade_sn'];

        $this->updateTradeToRefunded($tradeSn, $data);

        return $response;
    }

    protected function updateTradeToRefunded($tradeSn, $data)
    {
        $lockKey = "payment_trade_refunded_{$tradeSn}";
        $lock = $this->biz['lock'];
        $lock->get($lockKey);

        $trade = $this->getPayTradeDao()->getByTradeSn($tradeSn);
        if (RefundedStatus::NAME == $trade['status']) {
            return $trade;
        }

        $trade = $this->getTradeContext($trade['id'])->refunded($data);
        $lock->release($lockKey);

        return $trade;
    }

    protected function validateLogin()
    {
        if (empty($this->biz['user']['id'])) {
            throw new AccessDeniedException('user is not login.');
        }
    }

    protected function createPayTrade($data)
    {
        $rate = $this->getDefaultCoinRate();

        $trade = array(
            'title' => $data['goods_title'],
            'trade_sn' => $this->generateSn(),
            'order_sn' => $data['order_sn'],
            'platform' => $data['platform'],
            'platform_type' => $data['platform_type'],
            'price_type' => $this->getCurrencyType(),
            'amount' => $data['amount'],
            'rate' => $this->getDefaultCoinRate(),
            'seller_id' => empty($data['seller_id']) ? 0 : $data['seller_id'],
            'user_id' => $this->biz['user']['id'],
            'status' => 'paying',
        );

        if (!empty($data['type'])) {
            $trade['type'] = $data['type'];
        }

        if (empty($data['coin_amount'])) {
            $trade['coin_amount'] = 0;
        } else {
            $trade['coin_amount'] = $data['coin_amount'];
        }

        if ('money' == $trade['price_type']) {
            $amount = round($trade['amount'] * $trade['rate']);
            $trade['cash_amount'] = floor(($amount - $trade['coin_amount']) / $trade['rate']); // 标价为人民币，可用虚拟币抵扣
        } else {
            $trade['cash_amount'] = floor(($trade['amount'] - $trade['coin_amount']) / $rate); // 标价为虚拟币
        }

        if (0 == $trade['cash_amount'] && $trade['coin_amount'] > 0) {
            $trade['platform'] = 'none';
            $trade['platform_type'] = '';
        }

        return $this->getPayTradeDao()->create($trade);
    }

    protected function transfer($trade)
    {
        if (!empty($trade['cash_amount'])) {
            $flow = $this->getAccountService()->rechargeCash($trade);

            $fields = array(
                'from_user_id' => $trade['user_id'],
                'buyer_id' => $trade['user_id'],
                'to_user_id' => $trade['seller_id'],
                'amount' => $trade['cash_amount'],
                'title' => $trade['title'],
                'trade_sn' => $trade['trade_sn'],
                'order_sn' => $trade['order_sn'],
                'platform' => $trade['platform'],
                'parent_sn' => $flow['sn'],
                'currency' => $trade['currency'],
                'action' => $trade['type'],
            );
            $flow = $this->getAccountService()->transferCash($fields);
        }

        if ('recharge' == $trade['type']) {
            if (!empty($trade['cash_amount'])) {
                $fields = array(
                    'from_user_id' => $trade['seller_id'],
                    'to_user_id' => $trade['user_id'],
                    'buyer_id' => $trade['user_id'],
                    'amount' => $trade['cash_amount'] * $this->getDefaultCoinRate(),
                    'title' => $trade['title'],
                    'trade_sn' => $trade['trade_sn'],
                    'order_sn' => $trade['order_sn'],
                    'platform' => $trade['platform'],
                    'parent_sn' => empty($flow['sn']) ? '' : $flow['sn'],
                    'action' => 'recharge',
                );
                $this->getAccountService()->transferCoin($fields);
            }
        } elseif ('purchase' == $trade['type']) {
            if (!empty($trade['coin_amount'])) {
                $this->getAccountService()->releaseCoin($trade['user_id'], $trade['coin_amount']);

                $fields = array(
                    'from_user_id' => $trade['user_id'],
                    'to_user_id' => $trade['seller_id'],
                    'buyer_id' => $trade['user_id'],
                    'amount' => $trade['coin_amount'],
                    'title' => $trade['title'],
                    'trade_sn' => $trade['trade_sn'],
                    'order_sn' => $trade['order_sn'],
                    'platform' => $trade['platform'],
                    'parent_sn' => empty($flow['sn']) ? '' : $flow['sn'],
                    'action' => 'purchase',
                );
                $this->getAccountService()->transferCoin($fields);
            }
        }
    }

    protected function generateSn($prefix = '')
    {
        return $prefix.date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function getTargetlogService()
    {
        return $this->biz->service('Targetlog:TargetlogService');
    }

    protected function getPayTradeDao()
    {
        return $this->biz->dao('Pay:PayTradeDao');
    }

    protected function getAccountService()
    {
        return $this->biz->service('Pay:AccountService');
    }

    protected function getInvoiceService()
    {
        return $this->biz->service('Invoice:InvoiceService');
    }

    protected function getDefaultCoinRate()
    {
        $options = $this->biz['payment.final_options'];

        return empty($options['coin_rate']) ? 1 : $options['coin_rate'];
    }

    protected function getGoodsTitle()
    {
        $options = $this->biz['payment.final_options'];

        return empty($options['goods_title']) ? '' : mb_substr($options['goods_title'], 0, 30, 'utf-8');
    }

    protected function getCurrencyType()
    {
        return 'money';
    }

    protected function getPayment($payment)
    {
        return $this->biz['payment.'.$payment];
    }

    protected function createPaymentPlatformTrade($data, $trade)
    {
        $data['trade_sn'] = $trade['trade_sn'];
        unset($data['user_id']);
        unset($data['seller_id']);
        $data['amount'] = $trade['cash_amount'];
        $data['platform_type'] = $trade['platform_type'];
        $data['platform'] = $trade['platform'];

        if ($title = $this->getGoodsTitle()) {
            $data['goods_title'] = $title;
        }

        $result = $this->getPayment($data['platform'])->createTrade($data);

        return $this->getPayTradeDao()->update($trade['id'], array(
            'platform_created_result' => $result,
            'platform_created_params' => $data,
        ));
    }

    public function getCreateTradeResultByTradeSnFromPlatform($tradeSn)
    {
        $trade = $this->getPayTradeDao()->getByTradeSn($tradeSn);

        $result = $this->getPayment($trade['platform'])->createTrade($trade['platform_created_params']);

        $this->getPayTradeDao()->update($trade['id'], array(
            'platform_created_result' => $result,
        ));

        return $result;
    }

    public function getTradeByPlatformSn($platformSn)
    {
        return  $this->getPayTradeDao()->getByPlatformSn($platformSn);
    }

    protected function getTradeContext($id)
    {
        $tradeContext = $this->biz['payment_trade_context'];

        $trade = $this->getPayTradeDao()->get($id);
        if (empty($trade)) {
            throw $this->createNotFoundException("trade #{$trade['id']} is not found");
        }

        $tradeContext->setPayTrade($trade);

        return $tradeContext;
    }
}
