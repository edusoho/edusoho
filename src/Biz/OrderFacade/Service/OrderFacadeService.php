<?php

namespace Biz\OrderFacade\Service;

use Biz\OrderFacade\Product\Product;

interface OrderFacadeService
{
    public function create(Product $product);

    public function checkOrderBeforePay($sn, $params);

    public function getTradeShouldPayAmount($order, $coinAmount);
}
