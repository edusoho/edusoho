<?php

namespace ApiBundle\Api\Resource\LiveStatistic;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\LiveStatistics\Service\Impl\LiveCloudStatisticsServiceImpl;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;

class LiveStatisticDetail extends AbstractResource
{
    public function search(ApiRequest $request, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        if (empty($task)) {
            TaskException::NOTFOUND_TASK();
        }

        return $this->getLiveStatisticsService()->getLiveData($task);
    }

    /**
     * @return LiveCloudStatisticsServiceImpl
     */
    protected function getLiveStatisticsService()
    {
        return $this->service('LiveStatistics:LiveCloudStatisticsService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}
