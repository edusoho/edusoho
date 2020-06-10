<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class MemberException extends AbstractException
{
    const EXCEPTION_MODULE = 19;

    const NOTFOUND_MEMBER = 4041901;

    const FORBIDDEN_NOT_MEMBER = 4031902;

    const DUPLICATE_MEMBER = 4031903;

    const EXPIRED_MEMBER = 5001904;

    const MEMBER_NOT_STUDENT = 5001905;

    const NON_EXPIRED_MEMBER = 5001906;

    public $messages = [
        4041901 => 'exception.course.member.not_found',
        4031902 => 'exception.course.member.not_member',
        4031903 => 'exception.course.member.duplicate_member',
        5001904 => 'exception.course.member.expired_member',
        5001905 => 'exception.course.member.not_student',
        5001906 => 'exception.course.member.non_expired',
    ];
}
