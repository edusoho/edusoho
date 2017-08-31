<?php

namespace Biz\OrderFacade\Command\OrderPayCheck;

use Codeages\Biz\Framework\Context\BizAware;

abstract class OrderPayCheckCommand extends BizAware
{
    abstract public function execute($order, $params);
}
