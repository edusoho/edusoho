<?php

namespace Biz\BehaviorVerification;

use AppBundle\Common\Exception\AbstractException;

class AppException extends AbstractException
{
    const EXCEPTION_MODULE = 48;
    const GET_COORDINATE_FAILED = 5004801;

    public $messages = [
        5004801 => '短信发送异常。',
    ];
}