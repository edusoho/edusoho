<?php

namespace Biz\Product;

use AppBundle\Common\Exception\AbstractException;

class ProductException extends AbstractException
{
    const EXCEPTION_MODULE = 71;

    const NOTFOUND_PRODUCT = 4047101;

    public $messages = [
        self::NOTFOUND_PRODUCT => 'exception.product.not_exist',
    ];
}
