<?php

namespace ApiBundle\Api\Exception;

class AccessDeniedException extends ApiException
{
    const HTTP_CODE = 403;

    const TYPE = 'Access Denied';

    public function __construct($message = 'ACCESS DENIED', $code = 11, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}