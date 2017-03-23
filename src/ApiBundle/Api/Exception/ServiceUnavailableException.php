<?php

namespace ApiBundle\Api\Exception;

class ServiceUnavailableException extends ApiException
{
    const HTTP_CODE = 503;

    const TYPE = 'SERVICE_UNAVAILABLE';

    public function __construct($message = 'SERVICE_UNAVAILABLE', $code = 7, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}