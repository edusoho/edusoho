<?php

namespace Biz\Accessor;

abstract class AccessorAdapter implements AccessorInterface
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    abstract public function access($bean);

    protected function buildResult($code)
    {
        return $this->biz['accessor.join_course'][$code];
    }
}
