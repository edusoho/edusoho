<?php

namespace Biz\Task;

use AppBundle\Common\Exception\AbstractException;

class TaskException extends AbstractException
{
    const EXCEPTION_MODULE = 12;

    const TASK_NUM_LIMIT = 4031201;

    const NOTFOUND_TASK = 4041202;

    const UNPUBLISHED_TASK = 5001203;

    const ERROR_TASK_MODE = 5001204;

    const CAN_NOT_DO = 4031205;

    const CAN_NOT_FINISH = 4031206;

    const LOCKED_TASK = 4031207;

    const FORBIDDEN_CREATE_TASK = 4031208;

    const FORBIDDEN_UPDATE_TASK = 4031209;

    const FORBIDDEN_PUBLISH_TASK = 4031210;

    const FORBIDDEN_UNPUBLISH_TASK = 4031211;

    const FORBIDDEN_DELETE_TASK = 4031212;

    const LESSONID_INVALID = 5001213;

    const ACCESS_DENIED = 4031214;

    const CATEGORYID_INVALID = 5001215;

    const FORBIDDEN_PREVIEW_TASK = 4031216;

    const TYPE_INVALID = 5001217;

    const LIVE_REPLAY_NOT_FOUND = 4041218;

    const LIVE_REPLAY_INVALID = 5001219;

    public $messages = [
        4031201 => 'lesson_tasks_no_more_than_5',
        4041202 => 'exception.task.not_found',
        5001203 => 'exception.task.unpublished_task',
        5001204 => 'exception.task.task_mode_error',
        4031205 => 'exception.task.can_not_do',
        4031206 => 'exception.task.can_not_finish',
        4031207 => 'exception.task.task_is_locked',
        4031208 => 'exception.task.forbidden_create_task',
        4031209 => 'exception.task.forbidden_update_task',
        4031210 => 'exception.task.forbidden_publish_task',
        4031211 => 'exception.task.forbidden_unpublish_task',
        4031212 => 'exception.task.forbidden_delete_task',
        5001213 => 'exception.task.lessonid_invalid',
        4031214 => 'exception.task.access_denied',
        5001215 => 'exception.task.categoryid_invalid',
        4031216 => 'exception.task.forbidden_preview_task',
        5001217 => 'exception.task.type_invalid',
        4041218 => 'exception.task.live_replay_not_found',
        5001219 => 'exception.task.live_replay_invalid',
    ];
}
