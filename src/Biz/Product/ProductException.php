<?php

namespace Biz\Product;

use AppBundle\Common\Exception\AbstractException;

class ProductException extends AbstractException
{
    const EXCEPTION_MODULE = 71;

    public $messages = [];
}
