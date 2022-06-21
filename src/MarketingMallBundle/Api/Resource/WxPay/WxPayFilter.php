<?php

namespace MarketingMallBundle\Api\Resource\WxPay;

use ApiBundle\Api\Resource\Filter;

class WxPayFilter extends Filter
{
    protected $simpleFields = [
        'wxpay_enabled', 'wxpay_appid', 'wxpay_account', 'wxpay_key', 'wxpay_secret', 'wxpay_mp_secret',
    ];
}