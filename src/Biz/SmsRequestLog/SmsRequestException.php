<?php

namespace Biz\SmsRequestLog;

use AppBundle\Common\Exception\AbstractException;

class SmsRequestException extends AbstractException
{
    const EXCEPTION_MODULE = 84;
    const GET_COORDINATE_FAILED = 5008401;

    public $messages = [
        5008401 => 'exception.sms_request.abnormal_SMS_sending',
    ];
}