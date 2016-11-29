<?php
namespace Biz\Task\Strategy\Impl;


use Biz\Course\Service\CourseService;
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

    public function getTasksRenderPage()
    {
        return 'WebBundle:CourseManage:tasks.html.twig';
    }

    public function getCourseItemsRenderPage()
    {
        return 'WebBundle:CourseManage/Parts:list-item.html.twig';
    }


}