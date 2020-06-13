<?php

namespace Biz\Sms;

use AppBundle\Common\Exception\AbstractException;

class SmsException extends AbstractException
{
    const EXCEPTION_MODULE = 07;

    const FORBIDDEN_SMS_SETTING = 4030701;

    const FORBIDDEN_SMS_CODE_INVALID = 4030702;

    const ERROR_SMS_TYPE = 5000703;

    const FAILED_SEND = 5000704;

    const NOTFOUND_BIND_MOBILE = 4040705;

    const ERROR_MATCH_MOBILE_USERNAME = 5000706;

    const ERROR_MOBILE = 5000707;

    const NEED_WAIT = 4030708;

    public $messages = [
        4030701 => 'exception.sms.setting_enable',
        4030702 => 'exception.sms.code_invalid',
        5000703 => 'exception.sms.type_error',
        5000704 => 'exception.sms.send_failed',
        4040705 => 'exception.sms.bind_mobile_not_found',
        5000706 => 'exception.sms.mobile_username_not_match',
        5000707 => 'exception.sms.error_mobile',
        4030708 => 'exception.sms.need_wait',
    ];
}
