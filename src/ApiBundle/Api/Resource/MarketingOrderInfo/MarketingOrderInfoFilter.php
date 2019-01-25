<?php

namespace ApiBundle\Api\Resource\MarketingOrderInfo;

use ApiBundle\Api\Resource\Filter;

class MarketingOrderInfoFilter extends Filter
{
    protected $publicFields = array(
        'targetId', 'targetType', 'cover', 'title', 'mobile', 'payAmount', 'merchantName',
    );
}
