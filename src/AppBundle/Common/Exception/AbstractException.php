<?php

namespace AppBundle\Common\Exception;

class AbstractException extends \Symfony\Component\HttpKernel\Exception\HttpException
{
    public $statusCodes = array(
        404,
        403,
        500,
    );

    public function __construct($code)
    {
        $statusCode = substr($code, -strlen($code), 3);
        $statusCode = in_array($statusCode, $this->statusCodes) ? $statusCode : 500;
        $message = empty($this->messages[$code]) ? 'exception.common_error' : $this->messages[$code];

        parent::__construct($statusCode, $message, null, array(), $code);
    }

    public static function __callStatic($method, $arg)
    {
        $class = get_called_class();
        $code = constant($class.'::'.$method);

        return new $class($code);
    }
}
