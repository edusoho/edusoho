<?php

namespace Biz\Thread;

use AppBundle\Common\Exception\AbstractException;

class ThreadException extends AbstractException
{
    const EXCEPTION_MODUAL = 05;

    const FORBIDDEN_TIME_LIMIT = 4030501;

    public $messages = array(
        4030501 => 'exception.thread.frequent',
    );
}
