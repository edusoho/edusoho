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

    abstract public function getName();

    public function process($data = array())
    {
        throw new AccessDeniedException('can not change status to '.$this->getName());
    }

}