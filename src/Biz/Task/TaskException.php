<?php

namespace Biz\Task;

use AppBundle\Common\Exception\AbstractException;

class TaskException extends AbstractException
{
    const EXCEPTION_MODUAL = 12;

    const TASK_NUM_LIMIT = 4031201;

    public $messages = array(
        4031201 => 'lesson_tasks_no_more_than_5',
    );
}
