<?php

namespace Biz\Task\Strategy;

use Biz\Task\Visitor\CourseStrategyVisitorInterface;

interface CourseStrategy
{
    public function accept(CourseStrategyVisitorInterface $visitor);

    //任务的api
    public function createTask($field);

    public function updateTask($id, $fields);

    public function deleteTask($task);

    public function canLearnTask($task);

    public function publishTask($task);

    public function unpublishTask($task);

    public function prepareCourseItems($course, $tasks, $limitNum);

    public function getTasksTemplate();

    public function getTaskItemTemplate();

    public function getJsonTemplate($task);
}
