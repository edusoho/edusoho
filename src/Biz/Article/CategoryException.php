<?php

namespace Biz\Article;

use AppBundle\Common\Exception\AbstractException;

class CategoryException extends AbstractException
{
    const EXCEPTION_MODULE = 32;

    const NOTFOUND_CATEGORY = 4043201;

    const EMPTY_NAME = 5003202;

    const EMPTY_CODE = 5003203;

    const CODE_INVALID = 5003204;

    const CODE_NUMERIC_INVALID = 5003205;

    public $messages = [
        4043201 => 'exception.article.not_found_category',
        5003202 => 'exception.article.category_empty_name',
        5003203 => 'exception.article.category_empty_code',
        5003204 => 'exception.article.category_code_invalid',
        5003205 => 'exception.article.category_numeric_code',
    ];
}
