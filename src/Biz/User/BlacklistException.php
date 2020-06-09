<?php

namespace Biz\User;

use AppBundle\Common\Exception\AbstractException;

class BlacklistException extends AbstractException
{
    const EXCEPTION_MODULE = 41;

    const FORBIDDEN_TAKE_BLACKLIST = 4034101;

    const DUPLICATE_ADD = 5004102;

    const NOTFOUND_BLACKLIST = 4044103;

    public $messages = [
        4034101 => 'exception.blacklist.forbidden_take',
        5004102 => 'exception.blacklist.duplicate_add',
        4044103 => 'exception.blacklist.not_found',
    ];
}
