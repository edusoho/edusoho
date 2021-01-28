<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class CourseDraftException extends AbstractException
{
    const EXCEPTION_MODULE = 63;

    const NOTFOUND_DRAFT = 4046301;

    public $messages = [
        4046301 => 'exception.course.draft.not_found',
    ];
}
