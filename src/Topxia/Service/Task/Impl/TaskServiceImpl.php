<?php
namespace Topxia\Service\Task\Impl;

use Topxia\Service\Task\TaskService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class TaskServiceImpl extends BaseService implements TaskService
{
    public function getTask($id)
    {
        return $this->getTaskDao()->getTask($id);
    }

    public function getActiveTaskBy($userId, $taskType, $targetId, $targetType)
    {
        return $this->getTaskDao()->getActiveTaskBy($userId, $taskType, $targetId, $targetType);
    }

    public function findUserTasksByBatchIdAndTasktype($userId, $batchId, $taskType)
    {
        return $this->getTaskDao()->findUserTasksByBatchIdAndTasktype($userId, $batchId, $taskType);
    }

    public function addTask(array $fields)
    {
        return $this->getTaskDao()->addTask($fields);
    }

    public function updateTask($id, array $fields)
    {
        return $this->getTaskDao()->updateTask($id, $fields);
    }

    public function deleteTask($id)
    {
        return $this->getTaskDao()->deleteTask($id);
    }

    public function searchTasks($conditions, $orderBy, $start, $limit)
    {
        return $this->getTaskDao()->searchTasks($conditions, $orderBy, $start, $limit);
    }

    public function searchTaskCount($conditions)
    {
        return $this->getTaskDao()->searchTaskCount($conditions);
    }

    protected function getTaskDao()
    {
        return $this->createDao('Task.TaskDao');
    }

}  