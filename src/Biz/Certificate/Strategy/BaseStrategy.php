<?php

namespace Biz\Certificate\Strategy;

use Codeages\Biz\Framework\Context\Biz;

abstract class BaseStrategy
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    abstract public function getTargetModal();

    abstract public function count($conditions);

    abstract public function search($conditions, $orderBys, $start, $limit);
}
