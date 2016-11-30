<?php

namespace Biz\Task\Strategy\Impl;


use Biz\Task\Strategy\BaseLearningStrategy;
use Biz\Task\Strategy\LearningStrategy;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class LockModeStrategy extends BaseLearningStrategy implements LearningStrategy
{

    /**
     * 任务学习
     * @param $task
     * @return bool
     * @throws NotFoundException
     */
    public function canLearnTask($task)
    {
        if ($this->isFirstTask($task)) {
            return true;
        }

        $preTask = $this->getTaskDao()->getByCourseIdAndSeq($task['courseId'], $task['seq'] - 1);

        if ($preTask['isOptional']) {
            return true;
        }
        if (empty($preTask)) {
            throw new NotFoundException('previous task does not exist');
        }
        $isTaskLearned = $this->getTaskService()->isTaskLearned($preTask['id']);
        if ($isTaskLearned) {
            return true;
        }

        return false;
    }

    public function createTask($field)
    {
        return $this->baseCreateTask($field);
    }

    public function findCourseItems($courseId)
    {
        return $this->baseFindCourseItems($courseId);
    }

    public function getTasksRenderPage()
    {
        return 'WebBundle:CourseManage/LockMode:tasks.html.twig';
    }

    protected function isFirstTask($task)
    {
        return 1 == $task['seq'];
    }


}