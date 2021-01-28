<?php

namespace Codeages\Biz\Framework\Context;

abstract class BizAware
{
    /**
     * @var Biz
     */
    protected $biz;

    public function setBiz(Biz $biz)
    {
        $this->biz = $biz;
    }
}
