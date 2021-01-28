<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

class LianlianPayWapTrade extends LianlianPayWebTrade
{
    protected $payment = 'lianlianpay';

    protected $platformType = 'Wap';
}
