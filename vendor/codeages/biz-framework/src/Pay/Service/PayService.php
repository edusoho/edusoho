<?php

namespace Codeages\Biz\Framework\Pay\Service;

interface PayService
{
    public function findEnabledPayments();

    public function createTrade($trade);

    public function closeTradesByOrderSn($orderSn);

    public function applyRefundByTradeSn($tradeSn);

    public function notifyPaid($payment, $data);

    public function notifyRefunded($payment, $data);

    public function notifyClosed($data);

    public function queryTradeFromPlatform($tradeSn);
}