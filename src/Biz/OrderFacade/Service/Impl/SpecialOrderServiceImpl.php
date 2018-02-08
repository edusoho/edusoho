<?php

namespace Biz\OrderFacade\Service\Impl;

use Biz\OrderFacade\Service\SpecialOrderService;

class SpecialOrderServiceImpl extends SpecialOrderService
{
    public function beforeCreateOrder($orderFields, $params)
    {
        return $orderFields;
    }

    public function beforePayOrder($orderFields, $params)
    {
        return $orderFields;
    }
}
