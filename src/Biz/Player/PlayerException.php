<?php

namespace Biz\Player;

use AppBundle\Common\Exception\AbstractException;

class PlayerException extends AbstractException
{
    const EXCEPTION_MODUAL = 25;

    const NOT_SUPPORT_TYPE = 5002501;

    public $messages = array(
        5002501 => 'exception.player.not_support_type',
    );
}
