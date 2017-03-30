<?php

namespace Biz\Order\Service;

interface OrderFacadeService
{
    public function getOrderInfo($targetType, $targetId, $params);

    public function createOrder($targetType, $targetId, $params);
}