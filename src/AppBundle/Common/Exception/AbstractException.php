<?php

namespace AppBundle\Common\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class AbstractException extends HttpException
{
    const STATUS_CODE = array(
        404,
        403,
        500,
    );

    public $moduleCode;

    public $messages;

    public function __construct($code)
    {
        $codeArray = explode($code, '-');
        $statusCode = in_array($codeArray[0], self::STATUS_CODE) ? $codeArray[0] : 500;
        $message = empty($this->messages[$code]) ? '内部异常' : $this->messages[$code];

        parent::__construct($statusCode, $message, null, array(), $code);
    }
}
