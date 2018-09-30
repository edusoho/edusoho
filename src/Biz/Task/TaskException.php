<?php

namespace Biz\Task;

use AppBundle\Common\Exception\AbstractException;

class TaskException extends AbstractException
{
    const EXCEPTION_MODUAL = 12;

    const TASK_NUM_LIMIT = 4031201;

    const NOTFOUND_TASK = 4041202;

    const UNPUBLISHED_TASK = 5001203;

    public $messages = array(
        4031201 => 'lesson_tasks_no_more_than_5',
        4041202 => 'exception.task.not_found',
        5001203 => 'exception.task.unpublished_task',
    );
}
