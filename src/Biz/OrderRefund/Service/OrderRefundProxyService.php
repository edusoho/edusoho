<?php

namespace Biz\OrderRefund\Service;

use Codeages\Biz\Framework\Order\Service\OrderRefundService;

interface OrderRefundProxyService extends OrderRefundService
{
    public function applyOrderRefund($orderId, $fileds);

    public function cancelRefund($orderId);

    public function refuseRefund($orderId, $data);

    public function adoptRefund($orderId, $data);
}
