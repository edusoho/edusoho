<?php

namespace AppBundle\Common\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class AbstractException extends HttpException
{
    public $moduleCode;

    public $infos;

    public function __construct($code, $statuCode)
    {
        $info = $this->infos[$code];
        $code =

        parent::__construct($statusCode, $info['message'], null, array(), $code);
    }

    static public function getCode($code)
    {
        return  $info['statusCode'].$this->getModuleCode().$code;
    }

    abstract function getModuleCode();
}
