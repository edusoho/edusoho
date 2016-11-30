<?php

namespace Biz\Task\Strategy\Impl;


use Biz\Task\Strategy\BaseLearningStrategy;
use Biz\Task\Strategy\BaseStrategy;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Task\Strategy\LearningStrategy;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class PlanStrategy extends BaseStrategy implements CourseStrategy
{
    public function createTask($field)
    {
        return $this->baseCreateTask($field);
    }

    public function updateTask($id, $fields)
    {
        return $this->baseUpdateTask($id, $fields);
    }

    /**
     * 任务学习
     * @param $task
     * @return bool
     * @throws NotFoundException
     */
    public function canLearnTask($task)
    {
        $course = $this->getCourseService()->getCourse($task['courseId']);
        //自由式学习 可以学习任意课时
        if ($course['learnMode'] == 'freeMode') {
            return true;
        }
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