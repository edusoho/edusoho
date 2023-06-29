<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class LessonException extends AbstractException
{
    const EXCEPTION_MODULE = 13;

    const LESSON_NUM_LIMIT = 4031301;

    const NOTFOUND_LESSON = 4041302;

    const PARAMETERS_MISSING = 5001303;

    const TESTPAPER_DOTIMES_LIMIT = 5001304;

    const START_TIME_EARLIER = 5001305;

    const END_TIME_EARLIER = 5001306;

    public $messages = [
        4031301 => 'lesson_count_no_more_than_300',
        4041302 => 'exception.lesson.not_found',
        5001303 => 'exception.lesson.parameter_missing',
        5001304 => 'exception.lesson.testpaper_do_times_more_than_100',
        5001305 => 'exception.lesson.start_time_earlier_than_current',
        5001306 => 'exception.lesson.end_time_earlier_than_start_time',
    ];
}
