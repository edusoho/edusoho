<?php

namespace ApiBundle\Api\Exception;

class ExpiredCredentialException extends ApiException
{
    const HTTP_CODE = 401;

    const TYPE = 'EXPIRED_CREDENTIAL';

    public function __construct($message = 'EXPIRED_CREDENTIAL', $code = 4, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}