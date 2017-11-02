<?php

namespace Codeages\Biz\Order\Status;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

abstract class AbstractStatus
{
    protected $biz;

    function __construct($biz)
    {
        $this->biz = $biz;
    }

    abstract public function getName();

    public function process($data = array())
    {
        throw new AccessDeniedException('can not change status to '.$this->getName());
    }

}