<?php

namespace Biz\Article;

use AppBundle\Common\Exception\AbstractException;

class ArticleException extends AbstractException
{
    const EXCEPTION_MODUAL = 02;

    const NOTFOUND = 4040201;

    const PROPERTY_INVALID = 5000202;

    const DUPLICATE_LIKE = 5000203;

    const SOURCE_URL_INVALID = 5000204;

    const SORT_INVALID = 5000205;

    public $messages = array(
        4040201 => 'exception.article.notfound',
        5000202 => 'exception.article.error_property',
        5000203 => 'exception.article.duplicate_like',
        5000204 => 'exception.article.error_source_url',
        5000205 => 'exception.article.error_sort',
    );
}
