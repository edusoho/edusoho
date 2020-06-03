<?php

namespace Biz\System\Template;

use Codeages\Biz\Framework\Context\Biz;

abstract class Template
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function getTemplate();
}
