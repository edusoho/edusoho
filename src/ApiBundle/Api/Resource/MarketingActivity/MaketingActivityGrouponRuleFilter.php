<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\Converter;

class MaketingActivityGrouponRuleFilter extends Filter
{
    protected $publicFields = array(
        'id', 'activity_id', 'member_num', 'origin_price', 'owner_price', 'member_price', 'is_open_robot', 'created_time', 'updated_time',
    );

    protected function publicFields(&$data)
    {
        $fields = $data;

        $data = array(
            'id' => $fields['id'],
            'activityId' => $fields['activity_id'],
            'memberNum' => $fields['member_num'],
            'originPrice' => $fields['origin_price'] / 100,
            'ownerPrice' => $fields['owner_price'] / 100,
            'memberPrice' => $fields['member_price'] / 100,
            'isOpenRobot' => $fields['is_open_robot'],
            'createdTime' => $fields['created_time'],
            'updatedTime' => $fields['updated_time'],
        );

        Converter::timestampToDate($data['createdTime']);
        Converter::timestampToDate($data['updatedTime']);
    }
}
