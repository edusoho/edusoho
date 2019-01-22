<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\Converter;

class MaketingActivityCutRuleFilter extends Filter
{
    protected $publicFields = array(
        'id', 'activity_id', 'origin_price', 'lowest_price', 'reduce_amout', 'average', 'times', 'background_color', 'updated_time', 'created_time',
    );

    protected function publicFields(&$data)
    {
        $fields = $data;

        $data = array(
            'id' => $fields['id'],
            'activityId' => $fields['activity_id'],
            'originPrice' => $fields['origin_price'] / 100,
            'lowestPrice' => $fields['lowest_price'] / 100,
            'reduceAmout' => $fields['reduce_amout'] / 100,
            'average' => $fields['average'] / 100,
            'times' => $fields['times'],
            'backgroundColor' => $fields['background_color'],
            'createdTime' => $fields['created_time'],
            'updatedTime' => $fields['updated_time'],
        );

        Converter::timestampToDate($data['createdTime']);
        Converter::timestampToDate($data['updatedTime']);
    }
}
