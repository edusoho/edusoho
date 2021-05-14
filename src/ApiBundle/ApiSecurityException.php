<?php

namespace ApiBundle;

use AppBundle\Common\Exception\AbstractException;

class ApiSecurityException extends AbstractException
{
    const EXCEPTION_MODULE = 80;

    const SIGN_ERROR = 4038001;

    public $messages = [
        4038001 => 'exception.api_security.sign_error',
    ];
}
