<?php

namespace Biz\OrderFacade\Command\OrderPayCheck;

use Codeages\Biz\Framework\Context\BizAware;

abstract class OrderPayCheckCommand extends BizAware
{
    /**
     * @var OrderPayChecker
     */
    protected $orderPayChecker;

    abstract public function execute($order, $params);

    public function setOrderPayChecker($checker)
    {
        $this->orderPayChecker = $checker;
    }
}
