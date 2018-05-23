<?php

namespace Codeages\Biz\Framework\Context;

abstract class AbstractInterceptor
{
    public function __construct(Biz $biz, $className, &$interceptorData)
    {
        $this->biz = $biz;
        $this->className = $className;
        $this->interceptorData = $interceptorData;
    }

    abstract public function exec($value, $args);

}