<?php

namespace Topxia\Service\Task;

interface TaskService
{
    public function getTask($id);

    public function getTaskByParams(array $conditions);

    public function findUserTasksByBatchIdAndTaskType($userId, $batchId, $taskType);

    public function findUserCompletedTasks($userId, $batchId);

    public function addTask(array $fields);

    public function updateTask($id, array $fields);

    public function deleteTask($id);

    public function deleteTasksByBatchIdAndTaskTypeAndUserId($batchId, $taskType, $userId);

    public function searchTasks($conditions, $orderBy, $start, $limit);

    public function searchTaskCount($conditions);

    public function finishTask(array $targetObject, $taskType);

}