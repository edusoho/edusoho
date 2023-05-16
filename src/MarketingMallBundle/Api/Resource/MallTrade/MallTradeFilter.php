<?php

namespace MarketingMallBundle\Api\Resource\MallTrade;

use ApiBundle\Api\Resource\Filter;

class MallTradeFilter extends Filter
{
    protected $simpleFields = ['tradeSn', 'orderSn'];
}
