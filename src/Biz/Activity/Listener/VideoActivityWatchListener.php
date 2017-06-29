<?php

namespace Biz\Activity\Listener;

use Biz\Task\Service\TaskService;

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

        $this->getTaskService()->watchTask($data['task']['id'], $watchTime);
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
