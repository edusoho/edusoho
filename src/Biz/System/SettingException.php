<?php

namespace Biz\System;

use AppBundle\Common\Exception\AbstractException;

class SettingException extends AbstractException
{
    const EXCEPTION_MODULE = '08';

    const FORBIDDEN_MOBILE_REGISTER = 4030801;

    const FORBIDDEN_SMS_SEND = 4030802;

    const FORBIDDEN_NICKNAME_UPDATE = 4030803;

    const NOTFOUND_THIRD_PARTY_AUTH_CONFIG = 4040804;

    const FORBIDDEN_THIRD_PARTY_AUTH = 4030805;

    const NOT_SET_CLOUD_ACCESS_KEY = 5000806;

    const ERROR_CLOUD_ACCESS_KEY = 5000807;

    const OAUTH_CLIENT_TYPE_INVALID = 5000808;

    const CLOUD_VIDEO_DISABLE = 5000809;

    const COIN_IMG_SIZE_LIMIT = 5000810;

    const NO_COPYRIGHT = 5000811;

    const AI_FACE_DISABLE = 5000812;

    const FORBIDDEN_CLOUD_ATTACHMENT = 4030813;

    const APP_CLIENT_CLOSED = 4030814;

    public $messages = [
        4030801 => 'exception.setting.forbidden_mobile_register',
        4030802 => 'exception.sms.setting_enable',
        4030803 => 'exception.setting.forbidden_setting_nickname',
        4040804 => 'exception.setting.third_party_auth_not_found',
        4030805 => 'exception.setting.forbidden_third_party_auth',
        5000806 => 'exception.setting.cloud_access_key_not_set',
        5000807 => 'exception.setting.cloud_access_key_error',
        5000808 => 'exception.setting.oauth_client_type_invalid',
        5000809 => 'exception.setting.cloud_video_disable',
        5000810 => 'exception.setting.coin_img_size_limit',
        5000811 => 'exception.setting.no_copyright',
        5000812 => 'exception.setting.ai_face_disable',
        4030813 => 'exception.setting.attachment.setting_enable',
        4030814 => 'exception.setting.app_client_closed',
    ];
}
