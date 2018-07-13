<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

// only for app
class CoinTrade extends BaseTrade
{
    protected $payment = 'coin';

    protected $platformType = 'App';
}
