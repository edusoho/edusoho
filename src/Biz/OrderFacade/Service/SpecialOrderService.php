<?php

namespace Biz\OrderFacade\Service;

interface SpecialOrderService
{
    public function beforeCreateOrder($orderFields, $params);

    public function beforePayOrder($orderFields, $params);
}
