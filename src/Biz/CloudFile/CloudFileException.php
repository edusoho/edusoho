<?php

namespace Biz\CloudFile;

use AppBundle\Common\Exception\AbstractException;

class CloudFileException extends AbstractException
{
    const EXCEPTION_MODULE = 60;

    const NOTFOUND_CLOUD_FILE = 4046001;

    const NOTFOUND_PLAYER = 4046002;

    public $messages = [
        4046001 => 'exception.cloud_file.not_found',
        4046002 => 'exception.cloud_file.not_found_player',
    ];
}
