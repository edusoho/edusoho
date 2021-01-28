<?php

namespace Biz\Group;

use AppBundle\Common\Exception\AbstractException;

class GroupException extends AbstractException
{
    const EXCEPTION_MODULE = 36;

    const NOTFOUND_GROUP = 4043601;

    const DUPLICATE_JOIN = 5003602;

    const NOTFOUND_MEMBER = 4043603;

    const TITLE_REQUIRED = 5003604;

    public $messages = [
        4043601 => 'exception.group.not_found',
        5003602 => 'exception.group.duplicate_join',
        4043603 => 'exception.group.not_found_member',
        5003604 => 'exception.group.title_required',
    ];
}
