<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class CourseSetException extends AbstractException
{
    const EXCEPTION_MODULE = 17;

    const NOTFOUND_COURSESET = 4041701;

    const FORBIDDEN_MANAGE = 4031702;

    const SUB_COURSESET_EXIST = 5001703;

    const LIVE_STUDENT_NUM_REQUIRED = 5001704;

    const PUBLISHED_COURSE_REQUIRED = 5001705;

    const UNPUBLISHED_COURSESET = 5001706;

    const NO_COURSE = 5001707;

    const UNLOCK_ERROR = 5001708;

    const SOURCE_COURSE_CLOSED = 4031780;

    const SOURCE_COURSE_NOTFOUND = 4041781;

    const FORBIDDEN_CREATE = 4031703;

    public $messages = [
        4041701 => 'exception.courseset.not_found',
        4031702 => 'exception.courseset.forbidden_manage',
        5001703 => 'exception.courseset.sub_courseset_exist',
        5001704 => 'exception.courseset.live_student_num_required',
        5001705 => 'exception.courseset.published_course_required',
        5001706 => 'exception.courseset.unpublished',
        5001707 => 'exception.courseset.no_course',
        5001708 => 'exception.courseset.unlock_failed',
        4031780 => 'exception.courseset.source_course_closed',
        4041781 => 'exception.courseset.source_course_not_found',
        4031703 => 'exception.courseset.forbidden_create',
    ];
}
