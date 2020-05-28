<?php

namespace Biz\S2B2C;

use AppBundle\Common\Exception\AbstractException;

class S2B2CProductException extends AbstractException
{
    const EXCEPTION_MODULE = 70;

    const INVALID_S2B2C_PRODUCT_TYPE = 5007001;

    const INVALID_S2B2C_PRODUCT_UPDATE_TYPE = 5007002;

    const NOT_FOUND_PRODUCT = 4047003;

    public $message = [
        5007001 => 'exception.s2b2c_product.type_invalid',
        5007002 => 'exception.s2b2c_product.update_type_invalid',
        4047003 => 'exception.s2b2c_product.not_found',
    ];
}
