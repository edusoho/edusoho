<?php

namespace AppBundle\Component\Export\Bill;

class CoinBillExporter extends CashBillExporter
{
    public function getTitles()
    {
        return array(
            'cashflow.sn',
            'cashflow.title',
            'cashflow.order_sn',
            'cashflow.trade_sn',
            'cashflow.user_name',
            'cashflow.created_time',
            'cashflow.inflow',
            'cashflow.user_truename',
            'cashflow.user_email',
            'cashflow.user_mobile',
        );
    }

    public function buildCondition($conditions)
    {
        $conditions['user_id'] = 0;
        $conditions['amount_type'] = 'coin';

        return  $conditions;
    }
}
