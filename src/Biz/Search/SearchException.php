<?php

namespace Biz\Search;

use AppBundle\Common\Exception\AbstractException;

class SearchException extends AbstractException
{
    const EXCEPTION_MODUAL = 47;

    const SEARCH_FAILED = 5004701;

    public $messages = array(
        5004701 => 'exception.search.failed',
    );
}
