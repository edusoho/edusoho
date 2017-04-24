<?php

namespace Codeages\Biz\Framework\Context;

trait BizAwareTrait
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
