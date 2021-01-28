<?php

namespace Biz\Card;

use AppBundle\Common\Exception\AbstractException;

class CardException extends AbstractException
{
    const EXCEPTION_MODULE = 35;

    const TYPE_REQUIRED = 5003501;

    const TYPE_INVALID = 5003502;

    public $messages = [
        5003501 => 'exception.card.type_required',
        5003502 => 'exception.card.error_type',
    ];
}
