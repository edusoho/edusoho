<?php

namespace AppBundle\Component\Export\Bill;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Bill\CashBillExporter;

class CoinBillExporter extends CashBillExporter
{
    public function getTitles()
    {
        return array(
            'cashflow.sn',
            'cashflow.title',
            'cashflow.order_sn',
            'cashflow.user_name',
            'cashflow.created_time',
            'cashflow.number',
            'cashflow.platform',
            'cashflow.trade_sn',
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

    protected function getAccountProxyService()
    {
        return $this->getBiz()->service('Account:AccountProxyService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}