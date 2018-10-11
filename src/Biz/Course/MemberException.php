<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class MemberException extends AbstractException
{
    const EXCEPTION_MODUAL = 19;

    const NOTFOUND_MEMBER = 4041901;

    const FORBIDDEN_NOT_MEMBER = 4031902;

    public $messages = array(
        4041901 => 'exception.course.member.not_found',
        4031902 => 'exception.course.member.not_member',
    );
}
