<?php

namespace Biz\OrderFacade\Service\Impl;

use Biz\OrderFacade\Service\SpecialOrderService;
use Biz\BaseService;

class SpecialOrderServiceImpl extends BaseService implements SpecialOrderService
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
