<?php

namespace ApiBundle\Api\Exception;

class ResourceNotFoundException extends ApiException
{
    const HTTP_CODE = 404;

    const TYPE = 'RESOURCE_NOT_FOUND';

    public function __construct($message = 'RESOURCE_NOT_FOUND', $code = 9, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}