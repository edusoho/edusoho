<?php

namespace Biz\Taxonomy;

use AppBundle\Common\Exception\AbstractException;

class CategoryException extends AbstractException
{
    const EXCEPTION_MODULE = 21;

    const NOTFOUND_CATEGORY = 4042101;

    const NOTFOUND_GROUP = 4042102;

    const EMPTY_NAME = 5002103;

    const EMPTY_CODE = 5002104;

    const CODE_INVALID = 5002105;

    const CODE_DIGIT_INVALID = 5002106;

    const CODE_UNAVAILABLE = 5002107;

    const NOTFOUND_PARENT_CATEGORY = 4042108;

    public $messages = [
        4042101 => 'exception.category.not_found',
        4042102 => 'exception.category.not_found_group',
        5002103 => 'exception.category.empty_name',
        5002104 => 'exception.category.empty_code',
        5002105 => 'exception.category.code_invalid',
        5002106 => 'exception.category.code_digit_invalid',
        5002107 => 'exception.category.code_unavailable',
        4042108 => 'exception.category.not_found_parent_category',
    ];
}
