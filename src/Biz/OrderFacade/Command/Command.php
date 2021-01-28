<?php

namespace Biz\OrderFacade\Command;

use Biz\OrderFacade\Product\Product;
use Codeages\Biz\Framework\Context\BizAware;

abstract class Command extends BizAware
{
    abstract public function execute(Product $product, $params = array());
}
