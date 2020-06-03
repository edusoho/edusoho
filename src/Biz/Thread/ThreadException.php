<?php

namespace Biz\Thread;

use AppBundle\Common\Exception\AbstractException;

class ThreadException extends AbstractException
{
    const EXCEPTION_MODULE = 05;

    const FORBIDDEN_TIME_LIMIT = 4030501;

    const NOTFOUND_THREAD = 4040502;

    const NOTFOUND_POST = 4040503;

    const TITLE_REQUIRED = 5000504;

    const CONTENT_REQUIRED = 5000505;

    const TARGETID_REQUIRED = 5000506;

    const MEMBER_EXISTED = 5000507;

    const MEMBER_NUMBER_LIMIT = 5000508;

    const PARENTID_INVALID = 5000509;

    const TYPE_INVALID = 5000510;

    const NOTFOUND_MEMBER = 5000511;

    const ACCESS_DENIED = 4030512;

    public $messages = [
        4030501 => 'exception.thread.frequent',
        4040502 => 'exception.thread.not_found',
        4040503 => 'exception.thread.not_found_post',
        5000504 => 'exception.thread.title_required',
        5000505 => 'exception.thread.content_required',
        5000506 => 'exception.thread.targetid_required',
        5000507 => 'exception.thread.member_existed',
        5000508 => 'exception.thread.member_number_limit',
        5000509 => 'exception.thread.parentid_invalid',
        5000510 => 'exception.thread.type_invalid',
        5000511 => 'exception.thread.not_found_member',
        4030512 => 'exception.thread.access_denied',
    ];
}
