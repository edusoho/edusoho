<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

class AlipayLegacyWapTrade extends BaseTrade
{
    protected $payment = 'alipay';

    protected $platformType = 'Wap';
}
