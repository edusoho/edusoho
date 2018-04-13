<?php

namespace AppBundle\Common\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class AbstractException extends HttpException
{
    public $moduleCode;

    public $infos;

    public function __construct($code)
    {
        $info = $this->infos[$code];
        $code = $info['statusCode'].$this->moduleCode.$code;

        parent::__construct($statusCode, $info['message'], null, array(), $code);
    }
}
