<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

class LianlianPayWapTrade extends BaseTrade
{
    protected $payment = 'lianlianpay';

    protected $platformType = 'Wap';

}