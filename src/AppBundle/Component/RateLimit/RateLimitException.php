<?php

namespace AppBundle\Component\RateLimit;

use AppBundle\Common\Exception\AbstractException;

class RateLimitException extends AbstractException
{
    const EXCEPTION_MODUAL = 06;

    const FORBIDDEN_MAX_REQUEST = 4030602;

    const ERROR_CAPTCHA = 5000601;

    const FORBIDDEN_EMAIL_MAX_REQUEST = 4030603;

    public $messages = array(
        4030602 => 'exception.rate_limit_max_attempt_reach',
        5000601 => 'exception.rate_limit_error_captcha',
        4030603 => 'exception.rate_limit_email_max_attempt_reach',
    );
}
