<?php

namespace Biz\Common;

use AppBundle\Common\Exception\AbstractException;

class CommonException extends AbstractException
{
    const EXCEPTION_MODUAL = 03;

    const FORBIDDEN_DRAG_CAPTCHA_ERROR = 4030301;

    const FORBIDDEN_DRAG_CAPTCHA_EXPIRED = 4030302;

    const FORBIDDEN_DRAG_CAPTCHA_REQUIRED = 4030303;

    const FORBIDDEN_FREQUENT_OPERATION = 4030304;

    const ERROR_PARAMETER_MISSING = 5000305;

    const ERROR_PARAMETER = 5000306;

    public $messages = array(
        4030301 => 'exception.common_drag_captcha_error',
        4030302 => 'exception.common_drag_captcha_expired',
        4030303 => 'exception.common_drag_captcha_required',
        4030304 => 'exception.common_frequent_operation',
        5000305 => 'exception.common_parameter_missing',
        5000306 => 'exception.common_parameter_error',
    );
}
