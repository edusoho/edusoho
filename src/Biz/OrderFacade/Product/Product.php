<?php

namespace Biz\OrderFacade\Product;

use Codeages\Biz\Framework\Context\BizAware;

abstract class Product extends BizAware
{
    public $title;

    public $price;

    abstract public function init(array $params);

    abstract public function validate();
}
