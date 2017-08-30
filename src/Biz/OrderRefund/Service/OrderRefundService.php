<?php

namespace Biz\OrderRefund\Service;

use Biz\OrderRefund\Service;

interface OrderRefundService
{
    public function applyOrderRefund($orderId, $fileds);
}
