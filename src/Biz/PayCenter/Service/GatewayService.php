<?php

namespace Biz\PayCenter\Service;

interface GatewayService
{
    public function payOrder($orderId, $payment);
}
