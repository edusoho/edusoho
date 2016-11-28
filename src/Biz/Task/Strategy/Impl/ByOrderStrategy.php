<?php

namespace Biz\Task\Strategy\Impl;


use Biz\Task\Strategy\StrategyInterface;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Topxia\Service\Task\TaskService;

class ByOrderStrategy implements StrategyInterface
{
    private $biz = null;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    /**
     * 任务学习
     * @param $task
     * @return bool
     * @throws AccessDeniedException
     */
    public function canLearnTask($task)
    {
        if ($this->isFirstTask($task)) {
            return true;
        }

        if ($task['isOptional']) {
            return true;
        }

        $preTask = $this->getTaskDao()->getByCourseIdAndSeq($task['courseId'], $task['seq'] - 1);
        if (empty($preTask)) {
            throw new AccessDeniedException('previous task does not exist');
        }
        $isTaskLearned = $this->getTaskService()->isTaskLearned($preTask['id']);
        if ($isTaskLearned) {
            return true;
        }
    }

    protected function isFirstTask($task)
    {
        return 1 == $task['seq'];
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    protected function getTaskDao()
    {
        return $this->biz->service('Task:TaskDao');
    }

}