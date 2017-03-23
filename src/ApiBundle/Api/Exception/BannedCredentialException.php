<?php

namespace ApiBundle\Api\Exception;

class BannedCredentialException extends ApiException
{
    const HTTP_CODE = 401;

    const TYPE = 'BANNED_CREDENTIAL';

    public function __construct($message = 'BANNED_CREDENTIAL', $code = 5, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}