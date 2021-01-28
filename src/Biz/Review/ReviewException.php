<?php

namespace Biz\Review;

use AppBundle\Common\Exception\AbstractException;

class ReviewException extends AbstractException
{
    const EXCEPTION_MODULE = 73;

    const FORBIDDEN_CREATE_REVIEW = 4037301;

    const FORBIDDEN_OPERATE_REVIEW = 4037302;

    const NOT_FOUND_REVIEW = 4047301;

    const RATING_LIMIT = 5007301;

    const POST_LIMIT = 5007302;

    public $messages = [
        4037301 => 'exception.review.forbidden_create_review',
        4037302 => 'exception.review.forbidden_operate_review',
        4047301 => 'exception.review.not_found_review',
        5007301 => 'exception.review.rating_limit',
        5007302 => 'exception.review.post_limit',
    ];
}
