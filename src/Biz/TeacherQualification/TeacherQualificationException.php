<?php

namespace Biz\TeacherQualification;

use AppBundle\Common\Exception\AbstractException;

class TeacherQualificationException extends AbstractException
{
    const EXCEPTION_MODULE = 83;

    const  TEACHER_QUALIFICATION_NOT_ENABLE = 5008301;

    const  TEACHER_QUALIFICATION_NOT_TEACHER = 5008302;

    public $message = [
        '5008301' => 'exception.teacher_qualification_not_enable',
        '5008302' => 'exception.teacher_qualification_not_teacher',
    ];
}
