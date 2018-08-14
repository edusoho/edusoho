<?php

namespace Codeages\Biz\Framework\Context;

abstract class AbstractInterceptor
{
    public function __construct(Biz $biz, $className)
    {
        $this->biz = $biz;
        $this->className = $className;
    }

    abstract public function beforeExec($funcName, $args);

    abstract public function afterExec($funcName, $args, $result, $beforeResult = array());

    abstract public function getInterceptorData();
}
