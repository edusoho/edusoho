<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class LessonException extends AbstractException
{
    const EXCEPTION_MODUAL = 13;

    const LESSON_NUM_LIMIT = 4031301;

    public $messages = array(
        4031301 => 'lesson_count_no_more_than_300',
    );
}
