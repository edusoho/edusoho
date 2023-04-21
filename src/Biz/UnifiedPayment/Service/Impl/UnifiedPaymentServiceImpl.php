<?php

namespace Biz\UnifiedPayment\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\UnifiedPayment\Service\UnifiedPaymentService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Targetlog\Service\TargetlogService;
use Codeages\Biz\Pay\Payment\AbstractGateway;
use Codeages\Biz\Pay\Status\PayingStatus;
use Exception;

class UnifiedPaymentServiceImpl extends BaseService implements UnifiedPaymentService
{
    public function createTrade($fields)
    {
        $tradeFields = ['title', 'orderSn', 'amount', 'platform', 'platformType', 'userId', 'source'];
        $platformFields = ['description', 'notifyUrl', 'openId'];
        if (!ArrayToolkit::requireds($fields, array_merge($tradeFields, $platformFields))) {
            throw new InvalidArgumentException('trade args is invalid.');
        }

        $trade = [
            'title' => $fields['title'],
            'tradeSn' => $this->generateSn(),
            'orderSn' => $fields['orderSn'],
            'amount' => $fields['amount'],
            'platform' => $fields['platform'],
            'platformType' => $fields['platformType'],
            'userId' => $fields['userId'],
            'source' => $fields['source'],
            'sellerId' => $fields['sellerId'] ?? '',
            'status' => 'paying',
        ];

        //TODO 策略实现具体业务参数
        $trade['platformCreatedParams'] = [
            'goods_detail' => $fields['description'],
            'attach' => $fields['attach'] ?? [],
            'goods_title' => $fields['title'],
            'notify_url' => $fields['notifyUrl'],
            'open_id' => $fields['openId'],
            'trade_sn' => $trade['tradeSn'],
            'amount' => $trade['amount'],
            'platform_type' => $trade['platformType'],
            'platform' => $trade['platform'],
        ];
        $trade = $this->getTradeDao()->create($trade);
        $this->getTargetlogService()->log(TargetlogService::INFO, 'unified_payment.trade.create', $trade['tradeSn'], '创建订单', ['trade' => $trade, 'fields' => $fields]);

        return $trade;
    }

    public function notifyPaid($payment, $data)
    {
        list($data, $result) = $this->getPayment($payment)->converterNotify($data);
        $this->getTargetlogService()->log(TargetlogService::WARNING, 'unified_payment.trade.paid_notify', $data['tradeSn'], "收到第三方支付平台{$payment}的通知，交易号{$data['tradeSn']}，支付状态{$data['status']}", $data);

        $this->updateTradeToPaidAndTransferAmount($data);

        return $result;
    }

    protected function updateTradeToPaidAndTransferAmount($data)
    {
        if ('paid' !== $data['status']) {
            return $this->getTradeDao()->getByTradeSn($data['tradeSn']);
        }

        $trade = $this->getTradeDao()->getByTradeSn($data['tradeSn']);
        if (empty($trade)) {
            $this->getTargetlogService()->log(TargetlogService::WARNING, 'unified_payment.trade.not_found', $data['tradeSn'], "交易号{$data['tradeSn']}不存在", $data);

            return $trade;
        }

        try {
            $trade = $this->getTradeDao()->get($trade['id'], ['lock' => true]);

            if (PayingStatus::NAME != $trade['status']) {
                $this->getTargetlogService()->log(TargetlogService::WARNING, 'unified_payment.trade.is_not_paying', $data['tradeSn'], "交易号{$data['tradeSn']}状态不正确，状态为：{$trade['status']}", $data);
                return $trade;
            }
            if ($trade['amount'] != $data['pay_amount']) {
                $this->getTargetlogService()->log(TargetlogService::WARNING, 'unified_payment.trade.pay_amount.mismatch', $data['tradeSn'], "实际支付的价格与交易记录价格不匹配", ['trade' => $trade, 'data' => $data]);
            }

            $trade = $this->updateTradeToPaid($trade['id'], $data);

            $this->getTargetlogService()->log(TargetlogService::INFO, 'unified_payment.trade.paid', $data['tradeSn'], "交易号{$data['tradeSn']}，账目流水处理成功", $data);

        } catch (Exception $e) {
            $this->getTargetlogService()->log(TargetlogService::ERROR, 'unified_payment.trade.error', $data['tradeSn'], "交易号{$data['tradeSn']}处理失败, {$e->getMessage()}", $data);
            throw $e;
        }

        return $trade;
    }

    protected function updateTradeToPaid($tradeId, $data)
    {
        $updatedFields = [
            'status' => $data['status'],
            'pay_time' => $data['paid_time'],
            'platform_sn' => $data['cash_flow'],
            'notify_data' => $data,
            'currency' => $data['cash_type'],
        ];

        return $this->getTradeDao()->update($tradeId, $updatedFields);
    }

    protected function generateSn($prefix = ''): string
    {
        return $prefix . date('YmdHis', time()) . mt_rand(10000, 99999);
    }

    /**
     * @param $payment
     *
     * @return AbstractGateway
     */
    protected function getPayment($payment)
    {
        return $this->biz['payment.' . $payment];
    }

    protected function getTradeDao()
    {
        return $this->biz->dao('UnifiedPayment:TradeDao');
    }

    /**
     * @return TargetlogService
     */
    protected function getTargetlogService()
    {
        return $this->biz->service('Targetlog:TargetlogService');
    }
}
