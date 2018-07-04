<?php

namespace Biz\Sms;

use AppBundle\Common\Exception\AbstractException;

class SmsException extends AbstractException
{
    const EXCEPTION_MODUAL = 07;

    const FORBIDDEN_SMS_SETTING = 4030701;

    const FORBIDDEN_SMS_CODE_INVALID = 4030702;

    public $messages = array(
        4030701 => 'exception.sms.setting_enbale',
        4030702 => 'exception.sms.code_invalid',
    );
}
