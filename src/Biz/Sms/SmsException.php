<?php

namespace Biz\Sms;

use AppBundle\Common\Exception\AbstractException;

class SmsException extends AbstractException
{
    const EXCEPTION_MODUAL = 07;

    const FORBIDDEN_SMS_SETTING = 4030701;

    public $messages = array(
        4030701 => 'exception.sms.setting_enbale',
    );
}
