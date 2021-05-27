<?php

namespace Biz\MultiClass;

use AppBundle\Common\Exception\AbstractException;

class MultiClassException extends AbstractException
{
    const EXCEPTION_MODULE = 81;

    const MULTI_CLASS_PRODUCT_EXIST = 5008101;

    const PRODUCT_NOT_FOUND = 4048102;

    const CANNOT_DELETE_DEFAULT_PRODUCT = 4038103;

    const MULTI_CLASS_EXIST = 5008104;

    const MULTI_CLASS_NOT_EXIST = 4048105;

    const MULTI_CLASS_TEACHER_REQUIRE = 5008106;

    const MULTI_CLASS_ASSISTANT_REQUIRE = 5008107;

    const MULTI_CLASS_ASSISTANT_NUMBER_EXCEED = 5008108;

    const MULTI_CLASS_DATA_FIELDS_MISSING = 5008109;

    const MULTI_CLASS_COURSE_NOT_MATCH = 5008110;

    const MULTI_CLASS_CLONE_ALREADY = 5008111;

    const CAN_NOT_DELETE_PRODUCT = 4038112;

    const MULTI_CLASS_COURSE_EXIST = 5008112;

    public $messages = [
        5008101 => 'exception.multi_class.multi_class_product_exist',
        4048102 => 'exception.multi_class.product_not_found',
        4038103 => 'exception.multi_class.cannot_delete_default_product',
        5008104 => 'exception.multi_class.multi_class_exist',
        4048105 => 'exception.multi_class.multi_class_not_exist',
        5008106 => 'exception.multi_class.multi_class_teacher_require',
        5008107 => 'exception.multi_class.multi_class_assistant_require',
        5008108 => 'exception.multi_class.multi_class_assistant.number_exceed',
        5008109 => 'exception.multi_class.multi_class_data_fields_missing',
        5008110 => 'exception.multi_class.multi_class_course_not_match',
        5008111 => 'exception.multi_class.multi_class_clone_already',
        4038112 => 'exception.multi_class.can_not_delete_product',
        5008112 => 'exception.multi_class.multi_class_course_exist',
    ];
}
