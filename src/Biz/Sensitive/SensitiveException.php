<?php

namespace Biz\Sensitive;

use AppBundle\Common\Exception\AbstractException;

class SensitiveException extends AbstractException
{
    const EXCEPTION_MODULE = 04;

    const FORBIDDEN_WORDS = 4030401;

    public $messages = [
        4030401 => 'exception.sensitive.words',
    ];
}
