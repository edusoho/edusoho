<?php

namespace Codeages\Biz\Pay\Payment;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Pay\Message\CloseTradeResponse;

abstract class AbstractGateway
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function closeTrade($trade)
    {
        return new CloseTradeResponse(true);
    }

    abstract public function createTrade($data);

    abstract public function applyRefund($data);

    abstract public function queryTrade($tradeSn);

    /**
     * @param $data 第三方支付平台的通知信息
     *
     * @return array
     *               支付成功的返回值：
     *               status          success
     *               cash_flow       第三方支付平台的支付流水号
     *               paid_time       支付时间
     *               pay_amount      支付金额，整数，单位为分。
     *               cash_type       支付币种
     *               trade_no        订单号
     *               attach          附件字段
     *               notify_data     第三方通知的元信息
     *
     *      支付失败的返回值：
     *              status:         failture
     *              notify_data     第三方通知的元信息
     */
    abstract public function converterNotify($data);

    abstract public function converterRefundNotify($data);
}
