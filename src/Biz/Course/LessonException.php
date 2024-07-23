<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class LessonException extends AbstractException
{
    const EXCEPTION_MODULE = 13;

    const LESSON_NUM_LIMIT = 4031301;

    const NOTFOUND_LESSON = 4041302;

    const NOTFOUND_TICKET = 4041303;

    const PARAMETERS_MISSING = 5001303;

    public $messages = [
        4031301 => 'lesson_count_no_more_than_300',
        4041302 => 'exception.lesson.not_found',
        4041303 => 'exception.open_course_live_ticket.not_found',
        5001303 => 'exception.lesson.parameter_missing',
    ];
}
