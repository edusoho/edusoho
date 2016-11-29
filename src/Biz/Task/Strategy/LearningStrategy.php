<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 28/11/2016
 * Time: 11:21
 */

namespace Biz\Task\Strategy;


interface LearningStrategy
{
    public function createTask($field);

    public function canLearnTask($task);

    public function findCourseItems($courseId);

    /**
     * 任务列表管理页面
     * @return page path
     */
    public function getTasksRenderPage();

    /**
     * 任务列表片段页面
     * @return page path
     */
    public function getCourseItemsRenderPage();
}