<?php

namespace Biz\OrderFacade\Product;

interface Refund
{
    public function afterApplyRefund();

    public function afterAdoptRefund($order);

    public function afterCancelRefund();

    public function afterRefuseRefund($order);
}
