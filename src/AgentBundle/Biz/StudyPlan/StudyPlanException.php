<?php

namespace AgentBundle\Biz\StudyPlan;

use AppBundle\Common\Exception\AbstractException;

class StudyPlanException extends AbstractException
{
    const EXCEPTION_MODULE = 90;

    const LEARN_TASK_NOT_BE_EMPTY = 4009001;

    public $messages = [
        4009001 => 'learn.task.not.be.empty', // 学习计划任务不能为空
    ];
}
