<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 28/11/2016
 * Time: 11:31
 */

namespace Biz\Task\Strategy\Impl;


use Biz\Course\Service\CourseService;
use Biz\Task\Strategy\LearningStrategy;

class FreeOrderStrategy implements LearningStrategy
{
    private $biz = null;

    const COURSE_ITEM_RENDER_PAGE = 'WebBundle:CourseManage/Parts:list-item-freeOrder.html.twig';

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    /**
     * 自由学习
     * @param $task
     * @return bool
     */
    public function canLearnTask($task)
    {
        return true;
    }

    public function getCourseItems($courseId)
    {
        $courseItems = $this->getCourseService()->getCourseItems($courseId);
        return array($courseItems, self::COURSE_ITEM_RENDER_PAGE);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

}