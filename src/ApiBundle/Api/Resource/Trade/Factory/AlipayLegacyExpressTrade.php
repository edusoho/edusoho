<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

class AlipayLegacyExpressTrade extends BaseTrade
{
    protected $payment = 'alipay';

    protected $platformType = 'Web';
}
