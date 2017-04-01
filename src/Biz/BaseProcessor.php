<?php

namespace Biz;

use Codeages\Biz\Framework\Context\Biz;

abstract class BaseProcessor
{
    /**
     * @var Biz
     */
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function getBiz()
    {
        return $this->biz;
    }
}
