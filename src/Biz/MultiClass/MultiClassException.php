<?php

namespace Biz\MultiClass;

use AppBundle\Common\Exception\AbstractException;

class MultiClassException extends AbstractException
{
    const EXCEPTION_MODULE = 81;

    const MULTI_COURSE_PRODUCT_EXIST = 5008101;

    public $messages = [
        5008101 => 'exception.multi_course.multi_course_product_exist',
    ];
}
