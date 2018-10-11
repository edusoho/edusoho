<?php

namespace Biz\System;

use AppBundle\Common\Exception\AbstractException;

class SettingException extends AbstractException
{
    const EXCEPTION_MODUAL = '08';

    const FORBIDDEN_MOBILE_REGISTER = 4030801;

    const FORBIDDEN_SMS_SEND = 4030802;

    const FORBIDDEN_NICKNAME_UPDATE = 4030803;

    const NOTFOUND_THIRD_PARTY_AUTH_CONFIG = 4040804;

    const FORBIDDEN_THIRD_PARTY_AUTH = 4030805;

    const NOT_SET_CLOUD_ACCESS_KEY = 5000806;

    const ERROR_CLOUD_ACCESS_KEY = 5000807;

    public $messages = array(
        4030801 => 'exception.setting.forbidden_mobile_register',
        4030802 => 'exception.sms.setting_enbale',
        4030803 => 'exception.setting.forbidden_setting_nickname',
        4040804 => 'exception.setting.third_party_auth_not_found',
        4030805 => 'exception.setting.forbidden_third_party_auth',
        5000806 => 'exception.setting.cloud_access_key_not_set',
        5000807 => 'exception.setting.cloud_access_key_error',
    );
}
