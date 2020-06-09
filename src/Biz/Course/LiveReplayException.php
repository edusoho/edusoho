<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class LiveReplayException extends AbstractException
{
    const EXCEPTION_MODULE = 59;

    const NOTFOUND_LIVE_REPLAY = 4045901;

    public $messages = [
        4045901 => 'exception.live_replay.not_found',
    ];
}
