<?php

namespace Biz\Sign;

use AppBundle\Common\Exception\AbstractException;

class SignException extends AbstractException
{
    const EXCEPTION_MODUAL = 46;

    const DUPLICATE_SIGN = 4034601;

    public $messages = array(
        4034601 => 'exception.sign.duplicate_sign',
    );
}
