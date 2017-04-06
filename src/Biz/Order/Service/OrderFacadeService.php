<?php

namespace Biz\Order\Service;

interface OrderFacadeService
{
    public function getOrderInfo($targetType, $targetId, $fields);

    public function createOrder($targetType, $targetId, $fields);
}
