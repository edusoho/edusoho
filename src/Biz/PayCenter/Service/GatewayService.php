<?php

namespace Biz\PayCenter\Service;

interface GatewayService
{
    public function beforePayOrder($orderId, $targetType, $payment);
}
