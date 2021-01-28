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

    //任务列表
    public function getTasksListJsonData($task);

    //自由式：单个任务
    //传授式：课时以及任务
    public function getTasksJsonData($task);
}
