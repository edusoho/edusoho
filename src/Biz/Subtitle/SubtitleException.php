<?php

namespace Biz\Subtitle;

use AppBundle\Common\Exception\AbstractException;

class SubtitleException extends AbstractException
{
    const EXCEPTION_MODULE = 55;

    const NOTFOUND_SUBTITLE = 4045501;

    const COUNT_LIMIT = 5005502;

    public $messages = [
        4045501 => 'exception.subtitle.not_found',
        5005502 => 'exception.subtitle.no_more_than_4',
    ];
}
