<?php

namespace Biz\Task\Strategy\Impl;


use Biz\Task\Strategy\LearningStrategy;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Topxia\Service\Task\TaskService;

class ByOrderStrategy implements LearningStrategy
{
    private $biz = null;

    const COURSE_ITEM_RENDER_PAGE = 'WebBundle:CourseManage/Parts:list-item-byOrder.html.twig';

    public function __construct($biz)
    {
        $this->biz = $biz;
    }


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

    public function findCourseItems($courseId)
    {
        $courseItems = $this->getCourseService()->findCourseItems($courseId);
        return array($courseItems, self::COURSE_ITEM_RENDER_PAGE);
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

    protected function getCourseService()
    {
        return  $this->biz->service('Course:CourseService');
    }

}