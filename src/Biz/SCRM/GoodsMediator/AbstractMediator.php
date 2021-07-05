<?php

namespace Biz\SCRM\GoodsMediator;

use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractMediator
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function join($user, $specs, $context = []);
}
