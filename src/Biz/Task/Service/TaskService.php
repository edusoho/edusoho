<?php

namespace Biz\Task\Service;

interface TaskService
{
    public function getTask($id);

    public function getTaskByCourseIdAndActivityId($courseId, $activity);

    public function createTask($task);

    public function updateTask($id, $fields);

    public function deleteTask($id);

    public function findTasksByCourseId($courseId);

    public function findDetailedTasksByCourseId($courseId, $userId);

    public function findTaskResultsByCourseId($courseId, $userId);

    public function findTaskResults($couseTaskId, $userId);
}
