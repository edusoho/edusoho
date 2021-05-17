<?php

namespace Biz\MultiClass;

use AppBundle\Common\Exception\AbstractException;

class MultiClassException extends AbstractException
{
    const EXCEPTION_MODULE = 81;

    const MULTI_CLASS_PRODUCT_EXIST = 5008101;

    const MULTI_CLASS_EXIST = 5008102;

    const MULTI_CLASS_TEACHER_REQUIRE = 5008103;

    const MULTI_CLASS_ASSISTANT_OUT_MAX_NUMBER = 5008104;

    public $messages = [
        5008101 => 'exception.multi_class.multi_class_product_exist',
        5008102 => 'exception.multi_class.multi_class_exist',
        5008103 => 'exception.multi_class.multi_class_teacher_require',
        5008104 => 'exception.multi_class.multi_class_assistant_require',
        5008105 => 'exception.multi_class.multi_class_assistant.out_max_number',
    ];
}
