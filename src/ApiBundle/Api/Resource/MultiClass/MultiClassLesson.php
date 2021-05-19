<?php


namespace ApiBundle\Api\Resource\MultiClass;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;

class MultiClassLesson extends AbstractResource
{
    public function remove(ApiRequest $request, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);

        if (!$task) {
            throw TaskException::NOTFOUND_TASK();
        }

        $this->getTaskService()->deleteTask($taskId);

        return ['success' => true];
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}