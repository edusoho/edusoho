<?php

namespace Biz\Activity;

use AppBundle\Common\Exception\AbstractException;

class LiveActivityException extends AbstractException
{
    const EXCEPTION_MODULE = 51;

    const NOTFOUND_LIVE = 4045101;

    const ROOMTYPE_INVALID = 5005102;

    const LIVE_TIME_INVALID = 5005103;

    const CREATE_LIVEROOM_FAILED = 5005104;

    const LIVE_STATUS_INVALID = 5005105;

    public $messages = [
        4045101 => 'exception.live_activity.not_found',
        5005102 => 'exception.live_activity.roomtype_invalid',
        5005103 => 'exception.live_activity.live_time_invalid',
        5005104 => 'exception.live_activity.create_liveroom_failed',
        5005105 => 'exception.live_activity.live_status_invalid',
    ];
}
