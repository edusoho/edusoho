<?php

namespace Biz\OpenCourse;

use AppBundle\Common\Exception\AbstractException;

class OpenCourseException extends AbstractException
{
    const EXCEPTION_MODULE = 40;

    const NOTFOUND_OPENCOURSE = 4044001;

    const NOTFOUND_LESSON = 4044002;

    const LESSON_TYPE_INVALID = 5004003;

    const FAVOR_UNPUBLISHED = 5004004;

    const DUPLICATE_FAVOR = 5004005;

    const CANCEL_UN_FAVOR = 5004006;

    const STATUS_INVALID = 5004007;

    const FORBIDDEN_MANAGE_COURSE = 4034008;

    const ITEMIDS_INVALID = 5004009;

    const IS_NOT_MEMBER = 5004010;

    const CHECK_PASSWORD_REQUIRED = 5004011;

    public $messages = [
        4044001 => 'exception.opencourse.not_found',
        4044002 => 'exception.opencourse.not_found_lesson',
        5004003 => 'exception.opencourse.lesson_type_invalid',
        5004004 => 'exception.opencourse.favor_unpublished',
        5004005 => 'exception.opencourse.duplicate_favor',
        5004006 => 'exception.opencourse.cancel_unfavor',
        5004007 => 'exception.opencourse.status_invalid',
        4034008 => 'exception.opencourse.forbidden_manage_course',
        5004009 => 'exception.opencourse.itemids_invalid',
        5004010 => 'exception.opencourse.is_not_member',
        5004011 => 'exception.opencourse.check_password_required',
    ];
}
