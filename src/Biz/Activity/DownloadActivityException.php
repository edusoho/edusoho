<?php

namespace Biz\Activity;

use AppBundle\Common\Exception\AbstractException;

class DownloadActivityException extends AbstractException
{
    const EXCEPTION_MODUAL = 52;

    const NOT_DOWNLOAD_ACTIVITY = 5005201;

    const FILE_NOT_IN_ACTIVITY = 5005202;

    public $messages = array(
        5005201 => 'exception.download_activity.not_download_activity',
        5005202 => 'exception.download_activity.file_not_in_activity',
    );
}
