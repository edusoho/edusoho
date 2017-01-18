<?php

namespace Biz\Task\Strategy;


interface CourseStrategy
{
    //任务的api
    public function createTask($field);

    public function updateTask($id, $fields);

    public function deleteTask($task);

    public function canLearnTask($task);

    public function publishTask($task);

    public function unpublishTask($task);

    public function prepareCourseItems($course, $tasks);

    public function sortCourseItems($courseId, array $itemIds);

}