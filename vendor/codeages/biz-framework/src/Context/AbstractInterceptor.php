<?php

namespace Codeages\Biz\Framework\Context;

abstract class AbstractInterceptor
{
    public function __construct(Biz $biz, $className)
    {
        $this->biz = $biz;
        $this->className = $className;
    }

    abstract public function exec($funcName, $args);

    abstract public function getInterceptorData();
}
