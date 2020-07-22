<?php

namespace Biz\S2B2C;

use AppBundle\Common\Exception\AbstractException;

class S2B2CException extends AbstractException
{
    const EXCEPTION_MODULE = 73;

    const INVALID_S2B2C_HIDDEN_PERMISSION = 5007301;

    public $message = [
        5007301 => 'exception.s2b2c.hidden_permission_invalid',
    ];
}
