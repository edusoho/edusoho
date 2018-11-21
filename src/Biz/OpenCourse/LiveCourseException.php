<?php

namespace Biz\OpenCourse;

use AppBundle\Common\Exception\AbstractException;

class LiveCourseException extends AbstractException
{
    const EXCEPTION_MODUAL = 51;

    const CREATE_LIVEROOM_FAILED = 5005101;

    public $messages = array(
        5005101 => 'exception.livecourse.create_liveroom_failed',
    );
}
