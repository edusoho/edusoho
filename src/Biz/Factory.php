<?php

namespace Biz;

use Codeages\Biz\Framework\Context\Biz;

abstract class Factory
{
    private $biz;

    final public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function getBiz()
    {
        return $this->biz;
    }

    protected function setBiz(Biz $biz)
    {
        $this->biz = $biz;
    }
}
