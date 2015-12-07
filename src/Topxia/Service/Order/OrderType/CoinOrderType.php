<?php
namespace Topxia\Service\Order\OrderType;

use Topxia\Service\Common\ServiceKernel;

class CoinOrderType extends BaseOrderType implements OrderType
{
    public function getOrderBySn($sn)
    {
        return $this->getCashOrdersService()->getOrderBySn($sn);
    }

    protected function getCashOrdersService()
    {
        return ServiceKernel::instance()->createService('Cash.CashOrdersService');
    }
}
