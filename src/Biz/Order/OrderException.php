<?php

namespace Biz\Order;

use AppBundle\Common\Exception\AbstractException;

class OrderException extends AbstractException
{
    const EXCEPTION_MODUAL = '09';

    const NOTFOUND_ORDER = 4040901;

    const BEYOND_AUTHORITY = 4030902;

    const CLOSED_ORDER = 5000903;

    const UNKNOWN_ACTION = 5000904;

    public $messages = array(
        4040901 => 'exception.order.not_found',
        4030902 => 'exception.order.beyond_authority',
        5000903 => 'exception.order.closed_order',
        5000904 => 'exception.order.unknown_action'
    );
}
