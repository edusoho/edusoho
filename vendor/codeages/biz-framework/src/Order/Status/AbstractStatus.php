<?php

namespace Codeages\Biz\Framework\Order\Status;

abstract class AbstractStatus
{
    protected $biz;

    function __construct($biz)
    {
        $this->biz = $biz;
    }

    abstract public function getPriorStatus();
}