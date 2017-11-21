<?php

namespace Biz\Activity\Listener;

use Biz\Task\Service\TaskService;
use Biz\Xapi\Service\XapiService;

class VideoActivityWatchListener extends Listener
{
    public function handle($activity, $data)
    {
        if (!empty($data['watchTime']) && $data['watchTime'] <= TaskService::WATCH_TIME_STEP) {
            $watchTime = $data['watchTime'];
        } else {
            $watchTime = TaskService::WATCH_TIME_STEP;
        }
        if (empty($data['task'])) {
            return;
        }
        $this->getXapiService()->watchTask($data['task']['id'], $watchTime);
        $this->getTaskService()->watchTask($data['task']['id'], $watchTime);
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->getBiz()->service('Xapi:XapiService');
    }
}
