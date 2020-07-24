<?php

namespace Biz\S2B2C;

use AppBundle\Common\Exception\AbstractException;

class S2B2CProductException extends AbstractException
{
    const EXCEPTION_MODULE = 70;

    const INVALID_S2B2C_PRODUCT_TYPE = 5007001;

    const INVALID_S2B2C_PRODUCT_UPDATE_TYPE = 5007002;

    const NOT_FOUND_PRODUCT = 4047003;

    const REMOVE_PRODUCT_FAILED = 5007004;

    const ADOPT_PRODUCT_FAILED = 5007005;

    const ADOPT_PRODUCT_REPEAT = 5007006;

    const SYNC_PRODUCT_CONTENT_FAIL = 5007007;

    const PRODUCT_NOT_FOUNT = 5007008;

    const UPDATE_PRODUCT_VERSION_FAIL = 5007009;

    public $messages = [
        5007001 => 'exception.s2b2c_product.type_invalid',
        5007002 => 'exception.s2b2c_product.update_type_invalid',
        4047003 => 'exception.s2b2c_product.not_found',
        5007004 => 'exception.s2b2c_product.remove_failed',
        5007005 => 'exception.s2b2c_product.adopt_failed',
        5007006 => 'exception.s2b2c_product.adopt_repeat',
        5007007 => 'exception.s2b2c_product.sync_fail',
        5007008 => 'exception.s2b2c_product.not_found',
        5007009 => 'exception.s2b2c_product.update_version_fail',
    ];
}
