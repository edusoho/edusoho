<?php

namespace Biz\Content;

use AppBundle\Common\Exception\AbstractException;

class BlockException extends AbstractException
{
    const EXCEPTION_MODULE = 39;

    const NOTFOUND_BLOCK = 4043901;

    const NOTFOUND_TEMPLATE = 4043902;

    const EMPTY_HISTORY = 5003903;

    const EMPTY_CODES = 5003904;

    public $messages = [
        4043901 => 'exception.block.not_found',
        4043902 => 'exception.block.not_found_template',
        5003903 => 'exception.block.empty_history',
        5003904 => 'exception.block.empty_codes',
    ];
}
