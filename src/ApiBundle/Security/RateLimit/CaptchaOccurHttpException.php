<?php

namespace ApiBundle\Security\RateLimit;

use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class CaptchaOccurHttpException extends TooManyRequestsHttpException
{
    private $data;

    public function __construct($retryAfter = null, $message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct($retryAfter, $message, $previous, $code);
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}