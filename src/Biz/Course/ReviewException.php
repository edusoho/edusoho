<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class ReviewException extends AbstractException
{
    const EXCEPTION_MODUAL = 43;

    const NOTFOUND_REVIEW = 4044301;

    public $messages = array(
        4044301 => 'exception.review.not_found',
    );
}
