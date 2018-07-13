<?php

namespace ApiBundle\Api\Resource\Order;

use ApiBundle\Api\Resource\Filter;

class OrderFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'title', 'sn', 'pay_amount', 'created_time', 'status',
    );

    protected $publicFields = array(
        'price_amount', 'user_id', 'payment', 'platform_sn', 'pay_time', 'expired_refund_days',
    );
}
