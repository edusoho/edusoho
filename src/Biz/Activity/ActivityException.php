<?php

namespace Biz\Activity;

use AppBundle\Common\Exception\AbstractException;

class ActivityException extends AbstractException
{
    const EXCEPTION_MODULE = 24;

    const WATCH_LIMIT = 4032401;

    const NOTFOUND_ACTIVITY = 4042402;

    const LIVE_OVERLAP_TIME = 5002403;

    const ACTIVITY_NOT_IN_COURSE = 5002404;

    const ACTIVITY_NOT_MATCH_MEDIA = 5002405;

    public $messages = [
        4032401 => 'exception.activity.watch_video_limit',
        4042402 => 'exception.activity.not_found',
        5002403 => 'activity.live.overlap_time_notice',
        5002404 => 'exception.activity.not_in_course',
        5002405 => 'exception.activity.not_match_media',
    ];
}
