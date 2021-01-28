<?php

namespace Biz\Player;

use AppBundle\Common\Exception\AbstractException;

class PlayerException extends AbstractException
{
    const EXCEPTION_MODULE = 25;

    const NOT_SUPPORT_TYPE = 5002501;

    const FILE_TYPE_INVALID = 5002502;

    public $messages = [
        5002501 => 'exception.player.not_support_type',
        5002502 => 'exception.player.file_type_invalid',
    ];
}
