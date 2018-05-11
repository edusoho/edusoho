<?php

namespace AppBundle\Component\RateLimit;

use AppBundle\Common\Exception\AbstractException;

class RateLimitException extends AbstractException
{
    const EXCEPTION_MODUAL = 06;

    const FORBIDDEN_MAX_REQUEST = 4030602;

    const ERROR_CAPTCHA = 5000601;

    public $messages = array(
        4030602 => 'exception.rate_limit_max_attempt_reach',
        5000601 => 'exception.rate_limit_error_captcha',
    );
}
