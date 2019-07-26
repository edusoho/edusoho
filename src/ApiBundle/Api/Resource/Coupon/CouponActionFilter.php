<?php

namespace ApiBundle\Api\Resource\Coupon;

use ApiBundle\Api\Resource\Filter;

class CouponActionFilter extends Filter
{
    protected $publicFields = array(
        'success', 'message', 'data', 'error'
    );

    protected function publicFields(&$data)
    {
        if (isset($data['data']) && isset($data['data']['deadline'])) {
            $data['data']['deadline'] = date('c', $data['data']['deadline']);
        }

        if (isset($data['data']) && isset($data['data']['createdTime'])) {
            $data['data']['createdTime'] = date('c', $data['data']['createdTime']);
        }

        if (isset($data['data']) && isset($data['data']['receiveTime']) && ($data['data']['receiveTime'] != 0)) {
            $data['data']['receiveTime'] = date('c', $data['data']['receiveTime']);
        }
    }
}