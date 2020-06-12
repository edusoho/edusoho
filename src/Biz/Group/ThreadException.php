<?php

namespace Biz\Group;

use AppBundle\Common\Exception\AbstractException;

class ThreadException extends AbstractException
{
    const EXCEPTION_MODULE = 38;

    const NOTFOUND_THREAD = 4043801;

    const FORBIDDEN_TIME_LIMIT = 4033802;

    const TITLE_REQUIRED = 5003803;

    const CONTENT_REQUIRED = 5003804;

    const GROUPID_REQUIRED = 5003805;

    const USERID_REQUIRED = 5003806;

    const SORT_INVALID = 5003807;

    const COLLECT_OWN_THREAD = 5003808;

    const DUPLICATE_COLLECT = 5003809;

    const NOTFOUND_COLLECTION = 4043810;

    public $messages = [
        4043801 => 'exception.group.thread.not_found',
        4033802 => 'exception.thread.frequent',
        5003803 => 'exception.group.thread.title_required',
        5003804 => 'exception.group.thread.content_required',
        5003805 => 'exception.group.thread.groupid_required',
        5003806 => 'exception.group.thread.userid_required',
        5003807 => 'exception.group.thread.sort_invalid',
        5003808 => 'exception.group.thread.collect_own_thread',
        5003809 => 'exception.group.thread.already_collect',
        4043810 => 'exception.group.thread.not_found_collection',
    ];
}
