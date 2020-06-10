<?php

namespace Biz\Plumber;

use AppBundle\Common\Exception\AbstractException;

class PlumberException extends AbstractException
{
    const EXCEPTION_MODULE = 68;

    const NOT_FOUND_QUEUE = 5002501;

    public $messages = [
        5002501 => 'exception.plumber.not_found_queue',
    ];
}
