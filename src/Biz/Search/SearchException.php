<?php

namespace Biz\Search;

use AppBundle\Common\Exception\AbstractException;

class SearchException extends AbstractException
{
    const EXCEPTION_MODULE = 47;

    const SEARCH_FAILED = 5004701;

    public $messages = [
        5004701 => 'exception.search.failed',
    ];
}
