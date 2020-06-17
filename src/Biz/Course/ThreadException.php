<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class ThreadException extends AbstractException
{
    const EXCEPTION_MODULE = 53;

    const NOTFOUND_THREAD = 4045301;

    const NOT_MATCH_COURSE = 5005302;

    const NOTFOUND_POST = 4045303;

    const POST_NOT_MATCH_COURSE = 5005304;

    const TITLE_REQUIRED = 5005305;

    const TYPE_INVALID = 5005306;

    const COURSEID_REQUIRED = 5005307;

    public $messages = [
        4045301 => 'exception.course.thread.not_found',
        5005302 => 'exception.course.thread.not_in_course',
        4045303 => 'exception.course.thread.not_found_post',
        5005304 => 'exception.course.thread.post_not_in_course',
        5005305 => 'exception.course.thread.title_required',
        5005306 => 'exception.course.thread.type_invalid',
        5005307 => 'exception.course.thread.courseid_required',
    ];
}
