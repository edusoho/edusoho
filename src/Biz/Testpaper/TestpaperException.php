<?php

namespace Biz\Testpaper;

use AppBundle\Common\Exception\AbstractException;

class TestpaperException extends AbstractException
{
    const EXCEPTION_MODUAL = 22;

    const NOTFOUND_TESTPAPER = 4042201;

    const DRAFT_TESTPAPER = 4032202;

    const CLOSED_TESTPAPER = 4032203;

    const FORBIDDEN_RESIT = 4032204;

    const FORBIDDEN_ACCESS_TESTPAPER = 4032205;

    const FORBIDDEN_DUPLICATE_COMMIT = 4032206;

    const REVIEWING_TESTPAPER = 4032207;

    const NOT_TESTPAPER_TASK = 4032208;

    const NOTFOUND_EXERCISE = 4042209;

    const STATUS_INVALID = 5002210;

    const NOTFOUND_RESULT = 4042211;

    const MODIFY_COMMITTED_TESTPAPER = 5002212;

    public $messages = array(
        4042201 => 'exception.testpaper.not_found',
        4032202 => 'exception.testpaper.draft',
        4032203 => 'exception.testpaper.closed',
        4032204 => 'exception.testpaper.forbidden_resit',
        4032205 => 'exception.testpaper.forbidden_access_testpaper',
        4032206 => 'exception.testpaper.forbidden_duplicate_commit_testpaper',
        4032207 => 'exception.testpaper.reviewing',
        4032208 => 'exception.testpaper.not_testpaper_task',
        4042209 => 'exception.testpaper.not_found_exercise',
        5002210 => 'exception.testpaper.status_invalid',
        4042211 => 'exception.testpaper.not_found_result',
        5002212 => 'exception.testpaper.modify_committed_testpaper',
    );
}
