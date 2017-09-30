<?php

namespace ApiBundle\Api\Resource\Cashier\Trade;

use ApiBundle\Api\Resource\Cashier\BaseTrade;

class AlipayLegacyExpressTrade extends BaseTrade
{
    protected $payment = 'alipay';

    protected $platformType = 'Web';

}