<?php

namespace Biz\Task\Service;

interface TaskService
{
    const LEARN_TIME_STEP = 2;

    public function getTask($id);

    public function createTask($task);

    public function updateTask($id, $fields);

    public function deleteTask($id);

    public function findTasksByCourseId($courseId);

    public function findUserTasksByCourseId($courseId, $userId);

    public function startTask($taskId);

    public function doingTask($taskId, $time=TaskService::LEARN_TIME_STEP);

    public function finishTask($taskId);

    public function tryTakeTask($taskId);
}
