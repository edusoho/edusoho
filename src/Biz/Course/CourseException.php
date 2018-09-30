<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class CourseException extends AbstractException
{
    const EXCEPTION_MODUAL = 16;

    const NOTFOUND_COURSE = 4041601;

    const FORBIDDEN_TAKE_COURSE = 4031602;

    public $messages = array(
        4041601 => 'exception.course.not_found',
        4031602 => 'exception.course.forbidden_take_course',
    );
}