<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\Converter;

class MaketingActivitySeckillRuleFilter extends Filter
{
    protected $publicFields = array(
        'id', 'activity_id', 'origin_price', 'seckill_price', 'reduce_amount', 'product_sum', 'created_time', 'updated_time',
    );

    protected function publicFields(&$data)
    {
        $fields = $data;

        $data = array(
            'id' => $fields['id'],
            'activityId' => $fields['activity_id'],
            'originPrice' => $fields['origin_price'] / 100,
            'seckillPrice' => $fields['seckill_price'] / 100,
            'reduceAmount' => $fields['reduce_amount'] / 100,
            'productSum' => $fields['product_sum'],
            'createdTime' => $fields['created_time'],
            'updatedTime' => $fields['updated_time'],
        );

        Converter::timestampToDate($data['createdTime']);
        Converter::timestampToDate($data['updatedTime']);
    }
}
