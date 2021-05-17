<?php

namespace Biz\MultiClass;

use AppBundle\Common\Exception\AbstractException;

class MultiClassException extends AbstractException
{
    const EXCEPTION_MODULE = 81;

    const MULTI_CLASS_PRODUCT_EXIST = 5008101;

    const PRODUCT_NOT_FOUND = 4048102;

    public $messages = [
        5008101 => 'exception.multi_class.multi_class_product_exist',
        4048102 => 'exception.multi_class.product_not_found',
    ];
}
