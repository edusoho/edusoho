<?php

namespace Codeages\Biz\Framework\Service;

use Codeages\Biz\Framework\Context\Biz;

abstract class BaseService
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }
}
