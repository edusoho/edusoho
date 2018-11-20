<?php

namespace Biz\Coupon;

use AppBundle\Common\Exception\AbstractException;

class CouponException extends AbstractException
{
    const EXCEPTION_COUPON_RECEIVE_FAILED = 5005101;

    public $messages = array(
        5005101 => 'exception.coupon.receive.failed',
    );
}
