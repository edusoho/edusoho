<?php

namespace Biz\OrderRefund\Service;

interface OrderRefundService
{
    public function applyOrderRefund($orderId, $fileds);

    public function cancelRefund($orderId);

    public function refuseRefund($orderId, $data);

    public function adoptRefund($orderId, $data);
}
