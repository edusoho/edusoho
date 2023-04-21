<?php

namespace Biz\UnifiedPayment\Service;

interface UnifiedPaymentService
{
    public function getTradeByTradeSn(string $sn);

    public function createTrade($fields);

    public function createPlatformTradeByTradeSn($tradeSn);

    public function notifyPaid($payment, $data);
}
