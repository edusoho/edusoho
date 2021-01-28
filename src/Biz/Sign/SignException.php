<?php

namespace Biz\Sign;

use AppBundle\Common\Exception\AbstractException;

class SignException extends AbstractException
{
    const EXCEPTION_MODULE = 46;

    const DUPLICATE_SIGN = 4034601;

    public $messages = [
        4034601 => 'exception.sign.duplicate_sign',
    ];
}
