<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class MemberException extends AbstractException
{
    const EXCEPTION_MODUAL = 19;

    const NOTFOUND_MEMBER = 4041901;

    const FORBIDDEN_NOT_MEMBER = 4031902;

    const DUPLICATE_MEMBER = 4031903;

    const EXPIRED_MEMBER = 5001904;

    public $messages = array(
        4041901 => 'exception.course.member.not_found',
        4031902 => 'exception.course.member.not_member',
        4031903 => 'exception.course.member.duplicate_member',
        5001904 => 'exception.course.member.expired_member',
    );
}
