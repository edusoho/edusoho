<?php

namespace Biz\Task\Strategy;


interface CourseStrategy
{
    public function createTask($field);

    public function updateTask($id, $fields);

    public function canLearnTask($task);

    public function findCourseItems($courseId);

    /**
     * 任务列表管理页面
     * @return page path
     */
    public function getTasksRenderPage();

}