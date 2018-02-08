<?php

namespace Biz\Marketing\Service\Impl;

use Biz\OrderFacade\Service\Impl\OrderFacadeServiceImpl;

class MarketingOrderFacadeServiceImpl extends OrderFacadeServiceImpl
{
    protected function beforeCreateOrder($orderFields, $params)
    {
        $orderFields['expired_refund_days'] = $this->getRefundDays();

        return $orderFields;
    }

    protected function beforePayOrder($orderFields, $params)
    {
        $price = empty($params['create_extra']['price']) ? 0 : $params['create_extra']['price'];
        $orderFields['paid_cash_amount'] = $price * 100; //此时 price 单位为元，但paid_cash_amount单位为分
        $orderFields['pay_time'] = $params['pay_time'];

        return $orderFields;
    }
}
