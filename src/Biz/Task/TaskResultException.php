<?php

namespace Biz\Task;

use AppBundle\Common\Exception\AbstractException;

class TaskResultException extends AbstractException
{
    const EXCEPTION_MODULE = 64;

    const NOTFOUND_TASK_RESULT = 4046401;

    public $messages = [
        4046401 => 'exception.task_result.not_found',
    ];
}
