<?php

namespace Biz\Accessor;

class AccessorException extends \Symfony\Component\HttpKernel\Exception\HttpException
{
    const EXCEPTION_MODULE = 0;

    public static function __callStatic($method, $arg)
    {
        return new self(500, $arg);
    }
}
