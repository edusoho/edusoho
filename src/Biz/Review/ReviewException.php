<?php

namespace Biz\Review;

use AppBundle\Common\Exception\AbstractException;

class ReviewException extends AbstractException
{
    const EXCEPTION_MODULE = 73;

    const FORBIDDEN_CREATE_REVIEW = 4037301;

    public $messages = [
        4037301 => 'exception.review.forbidden_create_review',
    ];
}
