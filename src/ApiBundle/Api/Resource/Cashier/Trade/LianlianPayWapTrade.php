<?php

namespace ApiBundle\Api\Resource\Cashier\Trade;

use ApiBundle\Api\Resource\Cashier\BaseTrade;

class LianlianPayWapTrade extends BaseTrade
{
    protected $payment = 'lianlianpay';

    protected $platformType = 'Wap';

}