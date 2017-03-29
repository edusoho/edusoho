<?php

namespace ApiBundle\Api\Exception;

class InvalidCredentialException extends ApiException
{
    const HTTP_CODE = 401;

    const TYPE = 'INVALID_CREDENTIAL';

    public function __construct($message = 'INVALID_CREDENTIAL', $code = 4, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}