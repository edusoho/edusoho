<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class CourseSetException extends AbstractException
{
    const EXCEPTION_MODUAL = 17;

    const NOTFOUND_COURSESET = 4041701;

    public $messages = array(
        4041701 => 'exception.courseset.not_found',
    );
}