<?php

namespace Biz\System;

use AppBundle\Common\Exception\AbstractException;

class SettingException extends AbstractException
{
    const EXCEPTION_MODUAL = '08';

    const FORBIDDEN_MOBILE_REGISTER = 4030801;

    const FORBIDDEN_SMS_SEND = 4030802;

    public $messages = array(
        4030801 => 'exception.setting.forbidden_mobile_register',
        4030802 => 'exception.setting.forbidden_mobile_register',
    );
}
