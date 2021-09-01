<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class CourseNoteException extends AbstractException
{
    const EXCEPTION_MODULE = 42;

    const NOTFOUND_NOTE = 4044201;

    const NO_PERMISSION = 4034202;

    const DUPLICATE_LIKE = 5004203;

    const NOTFOUND_NOTE_LIKE = 5004204;

    public $messages = [
        4044201 => 'exception.course.note.not_found',
        4034202 => 'exception.course.note.no_permission',
        5004203 => 'exception.course.note.duplicate_like',
        5004204 => 'exception.course.note.like_not_found',
    ];
}
