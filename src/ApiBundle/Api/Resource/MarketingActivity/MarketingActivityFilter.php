<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\Resource\Filter;

class MarketingActivityFilter extends Filter
{
    protected $publicFields = array(
        'id', 'name', 'type', 'about', 'status', 'item_origin_price', 'play_price', 'lowest_price', 'shared_picture', 'created_time', 'start_time', 'end_time',
    );

    protected function publicFields(&$data)
    {
        $data['originPrice'] = $data['item_origin_price'];
        unset($data['item_origin_price']);
        $data['ownerPrice'] = $data['play_price'];
        unset($data['play_price']);
        $data['memberPrice'] = $data['lowest_price'];
        unset($data['lowest_price']);
        $data['cover'] = $data['shared_picture'];
        unset($data['shared_picture']);
        $data['createdTime'] = $data['created_time'];
        unset($data['created_time']);
        $data['startTime'] = $data['start_time'];
        unset($data['start_time']);
        $data['endTime'] = $data['end_time'];
        unset($data['end_time']);
    }
}
