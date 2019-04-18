<?php

namespace ApiBundle\Api\Resource\MarketingOrderInfo;

use ApiBundle\Api\Resource\Filter;

class MarketingOrderInfoFilter extends Filter
{
    protected $publicFields = array(
        'id', 'status', 'targetId', 'targetType', 'cover', 'title', 'mobile', 'payAmount', 'payApiUrl', 'merchantName', 'createdTime',
    );

    protected function publicFields(&$data)
    {
        $data['payAmount'] = sprintf('%.2f', $data['payAmount'] / 100);
    }
}
