<?php

namespace ApiBundle\Api\Exception;

class ApiException extends \Exception
{
    const HTTP_CODE = 500;

    const CODE = 7;

    const TYPE = 'INTERNAL_SERVER_ERROR';

    public function __construct($message = 'INTERNAL_SERVER_ERROR', $code = 7, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getHttpCode()
    {
        return static::HTTP_CODE;
    }

    public function getType()
    {
        return static::TYPE;
    }
}