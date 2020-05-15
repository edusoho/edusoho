<?php

namespace Biz\S2B2C;

use AppBundle\Common\Exception\AbstractException;

class S2B2CProductException extends AbstractException
{
    const EXCEPTION_MODULE = 70;

    const INVALID_S2B2C_PRODUCT_TYPE = 5007001;

    public $message = [
        5007001 => 'exception.s2b2c_product.type_invalid',
    ];
}
