<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\Converter;

class MeMarketingActivityFilter extends Filter
{
    protected $publicFields = array(
        'activityId', 'userId', 'name', 'type', 'status', 'cover', 'itemType', 'itemSourceId', 'originPrice', 'price', 'joinedTime',
    );

    protected function publicFields(&$data)
    {
        $data['originPrice'] = $data['originPrice'] / 100;
        $data['price'] = $data['price'] / 100;
        Converter::timestampToDate($data['joinedTime']);
    }
}
