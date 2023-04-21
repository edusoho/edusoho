<?php

namespace Biz\UnifiedPayment\Service;

interface UnifiedPaymentService
{
    public function createTrade($fields);

    public function notifyPaid($payment, $data);
}
