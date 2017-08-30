<?php

namespace Biz\OrderFacade\Product;

interface Refund
{
    public function applyRefund();

    public function cancelRefund();
}
