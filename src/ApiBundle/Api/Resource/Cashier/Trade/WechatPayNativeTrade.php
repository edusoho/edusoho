<?php

namespace ApiBundle\Api\Resource\Cashier\Trade;

use ApiBundle\Api\Resource\Cashier\BaseTrade;

class WechatPayNativeTrade extends BaseTrade
{
    protected $payment = 'wechat';

    protected $platformType = 'Native';
}