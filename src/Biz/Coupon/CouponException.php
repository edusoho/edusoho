<?php

namespace Biz\Coupon;

use AppBundle\Common\Exception\AbstractException;

class CouponException extends AbstractException
{
    const EXCEPTION_MODUAL = 45;

    const NOTFOUND_COUPON = 4044501;

    const STATUS_INVALID = 5004502;

    const TYPE_INVALID = 5004503;

    public $messages = array(
        4044501 => 'exception.coupon.not_found',
        5004502 => 'exception.coupon.status_invalid',
        5004503 => 'exception.coupon.type_invalid',
    );
}
