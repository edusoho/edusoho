<?php

namespace ApiBundle\Api\Resource\Cashier\Trade;

use ApiBundle\Api\Resource\Cashier\BaseTrade;

class LianlianPayWebTrade extends BaseTrade
{
    protected $payment = 'lianlianpay';

    protected $platformType = 'Web';

}