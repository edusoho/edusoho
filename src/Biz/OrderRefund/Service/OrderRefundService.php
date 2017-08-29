<?php

namespace Biz\OrderFacade\Service;

use Biz\OrderRefund\Service;

interface OrderRefundService
{
    public function applyOrderRefund();

    public function quitProduct();
}
