<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

class LianlianPayWebTrade extends BaseTrade
{
    protected $payment = 'lianlianpay';

    protected $platformType = 'Web';

}