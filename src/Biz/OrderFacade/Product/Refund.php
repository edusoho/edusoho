<?php

namespace Biz\OrderFacade\Product;

interface Refund
{
    public function afterApplyRefund();

    public function afterCancelRefund();

    public function afterAdoptRefund();
}
