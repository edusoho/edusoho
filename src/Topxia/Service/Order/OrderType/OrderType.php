<?php
namespace Topxia\Service\Order\OrderType;

interface OrderType
{
    public function getOrderBySn($sn);
}
