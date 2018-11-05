<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\Converter;

class MarketingActivityFilter extends Filter
{
    protected $publicFields = array(
        'id', 'name', 'type', 'about', 'status', 'item_origin_price', 'owner_price', 'member_price', 'item_cover', 'created_time', 'start_time', 'end_time',
    );

    protected function publicFields(&$data)
    {
        $data['originPrice'] = $data['item_origin_price'];
        unset($data['item_origin_price']);
        if (isset($data['owner_price'])) {
            $data['ownerPrice'] = $data['owner_price'];
            unset($data['owner_price']);
        }
        if (isset($data['member_price'])) {
            $data['memberPrice'] = $data['member_price'];
            unset($data['member_price']);
        }
        $data['cover'] = $data['item_cover'];
        unset($data['item_cover']);
        $data['createdTime'] = Converter::timestampToDate((int) $data['created_time']);
        unset($data['created_time']);
        $data['startTime'] = Converter::timestampToDate((int) $data['start_time']);
        unset($data['start_time']);
        $data['endTime'] = Converter::timestampToDate((int) $data['end_time']);
        unset($data['end_time']);
    }
}
