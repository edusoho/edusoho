<?php

namespace ApiBundle\Api\Resource\Cashier;

use ApiBundle\Api\Resource\Coupon\CouponFilter;
use ApiBundle\Api\Resource\Filter;

class CashierTradeFilter extends Filter
{
    protected $publicFields = array(
        'trade_sn'
    );

    public function filter(&$data)
    {

    }
}