<?php

namespace ApiBundle\Api\Exception;

class ApiNotFoundException extends ApiException
{
    const HTTP_CODE = 404;

    const TYPE = 'API_NOT_FOUND';

    public function __construct($message = 'API_NOT_FOUND', $code = 1, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}