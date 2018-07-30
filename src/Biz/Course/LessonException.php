<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class LessonException extends AbstractException
{
    const EXCEPTION_MODUAL = 11;

    const LESSON_NUM_LIMIT = 4031101;

    public $messages = array(
        4031101 => 'lesson_count_no_more_than_300',
    );
}
