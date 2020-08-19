<?php

namespace Biz\S2B2C;

use AppBundle\Common\Exception\AbstractException;

class S2B2CException extends AbstractException
{
    const EXCEPTION_MODULE = 73;

    const INVALID_S2B2C_HIDDEN_PERMISSION = 5007301;

    const SETTLEMENT_REPORT_NOT_FOUND = 5007302;

    public $message = [
        5007301 => 'exception.s2b2c.hidden_permission_invalid',
        5007302 => 'exception.s2b2c.settlement_report_not_found',
    ];
}
