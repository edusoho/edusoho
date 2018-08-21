<?php

namespace Biz\System;

use AppBundle\Common\Exception\AbstractException;

class SettingException extends AbstractException
{
    const EXCEPTION_MODUAL = '08';

    const FORBIDDEN_MOBILE_REGISTER = 4030801;

    const FORBIDDEN_SMS_SEND = 4030802;

    const FORBIDDEN_NICKNAME_UPDATE = 4030803;

    public $messages = array(
        4030801 => 'exception.setting.forbidden_mobile_register',
        4030802 => 'exception.sms.setting_enbale',
        4030803 => 'exception.setting.forbidden_setting_nickname'
    );
}
