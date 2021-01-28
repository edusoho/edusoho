<?php

namespace Biz\Testpaper;

use AppBundle\Common\Exception\AbstractException;

class HomeworkException extends AbstractException
{
    const NOTFOUND_HOMEWORK = 4046701;

    const DRAFT_HOMEWORK = 4036702;

    const CLOSED_HOMEWORK = 4036703;

    const FORBIDDEN_ACCESS_HOMEWORK = 4036704;

    const FORBIDDEN_DUPLICATE_COMMIT = 4036705;

    const REVIEWING_HOMEWORK = 4036706;

    const NOTFOUND_RESULT = 4046707;

    public $messages = array(
        4046701 => 'exception.homework.not_found',
        4036702 => 'exception.homework.draft',
        4036703 => 'exception.homework.closed',
        4036704 => 'exception.homework.forbidden_access_homework',
        4036705 => 'exception.homework.forbidden_duplicate_commit_homework',
        4036706 => 'exception.homework.reviewing',
        4046707 => 'exception.homework.not_found_result',
    );
}
