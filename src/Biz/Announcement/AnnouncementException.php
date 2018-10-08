<?php

namespace Biz\Announcement;

use AppBundle\Common\Exception\AbstractException;

class AnnouncementException extends AbstractException
{
    const EXCEPTION_MODUAL = 14;

    const ANNOUNCEMENT_NOT_FOUND = 4041401;

    public $messages = array(
        4041401 => 'exception.announcement.notfound',
    );
}
