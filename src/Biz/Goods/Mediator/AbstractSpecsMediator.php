<?php

namespace Biz\Goods\Mediator;

use Codeages\Biz\Framework\Context\Biz;
use Pimple\Container;

abstract class AbstractSpecsMediator
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Container $biz)
    {
        $this->biz = $biz;
    }

    abstract public function onCreate($target);

    abstract public function onUpdateNormalData($target);

    abstract public function onPriceUpdate($target);

    abstract public function onPublish($target);

    abstract public function onClose($target);

    abstract public function onDelete($target);
}
