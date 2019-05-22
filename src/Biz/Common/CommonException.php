<?php

namespace Biz\Common;

use AppBundle\Common\Exception\AbstractException;

class CommonException extends AbstractException
{
    const EXCEPTION_MODULE = 03;

    const FORBIDDEN_DRAG_CAPTCHA_ERROR = 4030301;

    const FORBIDDEN_DRAG_CAPTCHA_EXPIRED = 4030302;

    const FORBIDDEN_DRAG_CAPTCHA_REQUIRED = 4030303;

    const FORBIDDEN_FREQUENT_OPERATION = 4030304;

    const ERROR_PARAMETER_MISSING = 5000305;

    const ERROR_PARAMETER = 5000306;

    const FORBIDDEN_DRAG_CAPTCHA_FREQUENT = 5000307;

    const NOTFOUND_METHOD = 4040308;

    const PLUGIN_IS_NOT_INSTALL = 4040309;

    const NOTFOUND_SERVICE_PROVIDER = 4040310;

    const NOT_ALLOWED_METHOD = 4030311;

    const EXPIRED_UPLOAD_TOKEN = 5000312;

    const NOTFOUND_API = 4040313;

    public $messages = array(
        4030301 => 'exception.common_drag_captcha_error',
        4030302 => 'exception.common_drag_captcha_expired',
        4030303 => 'exception.common_drag_captcha_required',
        4030304 => 'exception.common_frequent_operation',
        5000305 => 'exception.common_parameter_missing',
        5000306 => 'exception.common_parameter_error',
        5000307 => 'exception.common_drag_captcha_frequent',
        4040308 => 'exception.common_method_not_found',
        4040309 => 'exception.common_plugin_is_not_install',
        4040310 => 'exception.common_service_provider_not_found',
        4030311 => 'exception.common_not_allowed_method',
        5000312 => 'exception.common_expired_upload_token',
        4040313 => 'exception.common_not_found_api',
    );
}
