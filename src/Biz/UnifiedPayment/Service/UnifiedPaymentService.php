<?php

namespace Biz\UnifiedPayment\Service;

interface UnifiedPaymentService
{
    public function isEnabledPlatform($platform);

    public function getTradeByTradeSn(string $sn);

    public function createTrade($fields);

    public function createPlatformTradeByTradeSn($tradeSn, $params);

    public function notifyPaid($payment, $data);

    public function refund($fields);

    public function closeTrade($sn);
}
