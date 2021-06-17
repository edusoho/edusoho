<?php

namespace Biz\WrongBook\Pool;

use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractPool
{
    public function __construct(Biz $biz)
    {
    }

    abstract public function getPoolTarget($report);
}
