<?php
namespace Biz\Task\Strategy\Impl;


use Biz\Task\Strategy\BaseLearningStrategy;
use Biz\Task\Strategy\LearningStrategy;
use Biz\Task\Strategy\page;

/**
 * 自由学习策略
 * Class FreeOrderStrategy
 * @package Biz\Task\Strategy\Impl
 */
class FreeOrderStrategy extends BaseLearningStrategy implements LearningStrategy
{

    public function canLearnTask($task)
    {
        return true;
    }

    public function createTask($field)
    {
        $task    = $this->baseCreateTask($field);
        $chapter = array(
            'courseId' => $task['courseId'],
            'title'    => $task['title'],
            'type'     => 'lesson'
        );
        $this->getCourseService()->createChapter($chapter);
        return $task;
    }


    public function getTasksRenderPage()
    {
        return 'WebBundle:CourseManage:tasks.html.twig';
    }

    public function getCourseItemsRenderPage()
    {
        return 'WebBundle:CourseManage/Parts:list-item.html.twig';
    }


}