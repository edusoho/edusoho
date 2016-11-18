<?php

namespace Biz\Task\Service;

interface TaskService
{
    public function getTask($id);

    public function createTask($task);

    public function updateTask($id, $fields);

    public function deleteTask($id);

    public function findTasksByCourseId($courseId);

    public function findUserTasksByCourseId($courseId, $userId);

    public function taskStart($taskId);

    public function taskFinish($taskId);

    public function tryTakeTask($taskId);
}
