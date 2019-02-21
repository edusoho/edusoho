<?php

namespace Biz\Coupon;

use AppBundle\Common\Exception\AbstractException;

class CouponException extends AbstractException
{
    const EXCEPTION_MODUAL = 45;

    const NOTFOUND_COUPON = 4044501;

    const STATUS_INVALID = 5004502;

    const TYPE_INVALID = 5004503;

    const RECEIVE_FAILED = 5004504;

    const PLUGIN_NOT_INSTALLED = 5004505;

    const INVALID = 4044506;

    const RECEIVED = 5004507;

    const FINISHED = 5004508;

    public $messages = array(
        4044501 => 'exception.coupon.not_found',
        5004502 => 'exception.coupon.status_invalid',
        5004503 => 'exception.coupon.type_invalid',
        5004504 => 'exception.coupon.receive.failed',
        5004505 => 'exception.coupon.plugin.not.installed',
        4044506 => 'exception.coupon.invalid',
        5004507 => 'exception.coupon.received',
        5004508 => 'exception.coupon.finished',
    );
}
