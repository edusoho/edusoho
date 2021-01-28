<?php

namespace Biz\Announcement;

use AppBundle\Common\Exception\AbstractException;

class AnnouncementException extends AbstractException
{
    const EXCEPTION_MODULE = 14;

    const ANNOUNCEMENT_NOT_FOUND = 4041401;

    const TYPE_INVALID = 5001402;

    public $messages = [
        4041401 => 'exception.announcement.notfound',
        5001402 => 'exception.announcement.type_invalid',
    ];
}
