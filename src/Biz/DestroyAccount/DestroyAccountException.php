<?php

namespace Biz\DestroyAccount;

use AppBundle\Common\Exception\AbstractException;

class DestroyAccountException extends AbstractException
{
    const EXCEPTION_MODULE = 67;

    const NOT_FOUND_RECORD = 4046701;

    const REASON_TOO_LONG = 5006702;

    const AUDIT_RECORD_EXIST = 5006703;

    public $messages = [
        4046701 => 'exception.destroy_account.not_found_record',
        5006702 => 'exception.destroy_account.reason_too_long',
        5006703 => 'exception.destroy_account.audit_record_exist',
    ];
}
