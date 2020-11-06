<?php

namespace Biz\Favorite\Types;

use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractType
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function getTarget($favorite);
}
