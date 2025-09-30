<?php

namespace Biz\Mail;

use AppBundle\Common\Exception\AbstractException;

class MailException extends AbstractException
{
    const EXCEPTION_MODULE = 93;

    const EMAIL_CODE_INVALID = 4039301;

    public $messages = [
        4039301 => 'exception.email.code_invalid',
    ];
}
