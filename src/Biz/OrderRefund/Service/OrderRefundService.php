<?php

namespace Biz\OrderRefund\Service;

interface OrderRefundService
{
    public function applyOrderRefund($orderId, $fileds);
}
