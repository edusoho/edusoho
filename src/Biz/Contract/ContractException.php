<?php

namespace Biz\Contract;

use AppBundle\Common\Exception\AbstractException;

class ContractException extends AbstractException
{
    const EXCEPTION_MODULE = 50;

    const SIGN_RECORD_IS_EXISTED = 5005001;

    public $messages = [
        5005001 => 'exception.sign_record.is_existed',
    ];
}
