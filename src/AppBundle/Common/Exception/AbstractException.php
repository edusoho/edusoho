<?php

namespace AppBundle\Common\Exception;

class AbstractException extends \Symfony\Component\HttpKernel\Exception\HttpException
{
    const STATUS_CODE = array(
        404,
        403,
        500,
    );
    
    public function __construct($code)
    {
        $statusCode = substr($code, -strlen($code), 3);
        $message = empty($this->messages[$code]) ? '内部异常' : $this->messages[$code];

        parent::__construct($statusCode, $message, null, array(), $code);
    }
}
