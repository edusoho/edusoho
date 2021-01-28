<?php

namespace Biz\CloudPlatform\Service;

use Codeages\Biz\Framework\Context\Biz;

abstract class BaseFacade
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }
}
