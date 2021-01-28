<?php

namespace Biz\Classroom;

use AppBundle\Common\Exception\AbstractException;

class ClassroomReviewException extends AbstractException
{
    const EXCEPTION_MODULE = 54;

    const NOTFOUND_REVIEW = 4045401;

    const RATING_LIMIT = 5005402;

    const PERMISSION_DENIED = 4035403;

    public $messages = [
        4045401 => 'exception.classroom.review.not_found',
        5005402 => 'exception.classroom.review.no_more_than_5',
        4035403 => 'exception.classroom.review.permission_denied',
    ];
}
