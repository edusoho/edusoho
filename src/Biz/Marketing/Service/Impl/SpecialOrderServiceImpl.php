<?php

namespace Biz\Marketing\Service\Impl;

use Biz\OrderFacade\Service\SpecialOrderService;
use Biz\BaseService;

class SpecialOrderServiceImpl extends BaseService implements SpecialOrderService
{
    public function beforeCreateOrder($orderFields, $params)
    {
        $orderFields['expired_refund_days'] = $this->getOrderFacadeService()->getRefundDays();

        return $orderFields;
    }

    public function beforePayOrder($orderFields, $params)
    {
        $price = empty($params['create_extra']['price']) ? 0 : $params['create_extra']['price'];
        $orderFields['paid_cash_amount'] = $price * 100; //此时 price 单位为元，但paid_cash_amount单位为分
        $orderFields['pay_time'] = $params['pay_time'];

        return $orderFields;
    }

    protected function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }
}
