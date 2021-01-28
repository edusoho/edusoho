<?php

namespace AppBundle\Common\Exception;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class BaseException extends HttpException
{
    public function __construct($statusCode, $message = null, \Exception $previous = null, array $headers = array(), $code = 0)
    {
        if (is_array($message) && count($message) >= 2 && is_array($message[1])) {
            $messageString = $this->trans($message[0], $message[1]);
            if (isset($message[2]) && is_string($message[2])) {
                $messageString = $this->trans($message[2]).':'.$messageString;
            }
        } else {
            $messageString = $this->trans($message);
        }
        parent::__construct($statusCode, $messageString, $previous, $headers, $code);
    }

    private function trans($message, $arguments = array())
    {
        return ServiceKernel::instance()->trans($message, $arguments);
    }
}
