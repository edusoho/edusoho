<?php

namespace Biz\Cashier\Service\Impl;

use Biz\BaseService;
use Biz\Cashier\Service\CashierService;
use Codeages\Biz\Pay\Service\PayService;

class CashierServiceImpl extends BaseService implements CashierService
{
    public function createTrade($trade)
    {
        return $this->getPayService()->createTrade($trade);
    }

    /**
     * @return PayService
     */
    private function getPayService()
    {
        return $this->createService('Pay:PayService');
    }
}
