<?php

namespace Biz\MultiClass;

use AppBundle\Common\Exception\AbstractException;

class MultiClassException extends AbstractException
{
    const EXCEPTION_MODULE = 81;

    const MULTI_CLASS_PRODUCT_EXIST = 5008101;

    const MULTI_CLASS_EXIST = 5008151;

    const MULTI_CLASS_NOT_EXIST = 5008152;

    const MULTI_CLASS_TEACHER_REQUIRE = 5008153;

    const MULTI_CLASS_ASSISTANT_REQUIRE = 5008153;

    const MULTI_CLASS_ASSISTANT_OUT_MAX_NUMBER = 5008154;

    public $messages = [
        5008101 => 'exception.multi_class.multi_class_product_exist',
        5008151 => 'exception.multi_class.multi_class_exist',
        5008152 => 'exception.multi_class.multi_class_not_exist',
        5008153 => 'exception.multi_class.multi_class_teacher_require',
        5008154 => 'exception.multi_class.multi_class_assistant_require',
        5008155 => 'exception.multi_class.multi_class_assistant.out_max_number',
    ];
}
