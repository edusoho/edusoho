<?php

namespace Biz\Task\Strategy\Impl;


use Biz\Task\Strategy\BaseLearningStrategy;
use Biz\Task\Strategy\LearningStrategy;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class ByOrderStrategy extends BaseLearningStrategy implements LearningStrategy
{

    const COURSE_ITEM_RENDER_PAGE = 'WebBundle:CourseManage/Parts:list-item-byOrder.html.twig';

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


    public function getTasksRenderPage()
    {
        return 'WebBundle:CourseManage:tasks-byOrder.html.twig';
    }

    public function getCourseItemsRenderPage()
    {
        return 'WebBundle:CourseManage/Parts:list-item-byOrder.html.twig';
    }


    protected function isFirstTask($task)
    {
        return 1 == $task['seq'];
    }




}