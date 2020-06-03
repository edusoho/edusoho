<?php

namespace Biz\Order;

use AppBundle\Common\Exception\AbstractException;

class OrderException extends AbstractException
{
    const EXCEPTION_MODULE = '09';

    const NOTFOUND_ORDER = 4040901;

    const BEYOND_AUTHORITY = 4030902;

    const CLOSED_ORDER = 5000903;

    const UNKNOWN_ACTION = 5000904;

    const VIP_ORDER_NOT_EXIST = 4030905;

    const NOTFOUND_ORDER_ITEMS = 4040906;

    public $messages = [
        4040901 => 'exception.order.not_found',
        4030902 => 'exception.order.beyond_authority',
        5000903 => 'exception.order.closed_order',
        5000904 => 'exception.order.unknown_action',
        4030905 => 'exception.order.vip_order_not_exist',
        4040906 => 'exception.order.not_found_order_items',
    ];
}
