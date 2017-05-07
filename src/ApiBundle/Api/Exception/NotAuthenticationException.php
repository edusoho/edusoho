<?php

namespace ApiBundle\Api\Exception;

class NotAuthenticationException extends ApiException
{
    const HTTP_CODE = 401;

    const TYPE = 'NOT_AUTHENTICATION';

    public function __construct($message = 'NOT_AUTHENTICATION', $code = 12, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}