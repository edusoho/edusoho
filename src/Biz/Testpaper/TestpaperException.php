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

    const FORBIDDEN_ACCESS_OTHER_STUDENT_TESTPAPER = 4032205;

    const FORBIDDEN_DUPLICATE_COMMIT = 4032206;

    const REVIEWING_TESTPAPER = 4032207;

    public $messages = array(
        4042201 => 'exception.testpaper.not_found',
        4032202 => 'exception.testpaper.draft',
        4032203 => 'exception.testpaper.closed',
        4032204 => 'exception.testpaper.forbidden_resit',
        4032205 => 'exception.testpaper.forbidden_access_other_student_testpaper',
        4032206 => 'exception.testpaper.forbidden_duplicate_commit_testpaper',
        4032207 => 'exception.testpaper.reviewing',
    );
}