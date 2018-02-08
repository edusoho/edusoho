<?php

namespace ApiBundle\Api\Resource\Coupon;

use ApiBundle\Api\Resource\Filter;

class CouponFilter extends Filter
{
    protected $publicFields = array(
        'id', 'code', 'type', 'status', 'rate', 'userId', 'deadline', 'targetType', 'targetId',
    );

    protected function publicFields(&$data)
    {
        $data['deadline'] = date('c', $data['deadline']);
    }
}
