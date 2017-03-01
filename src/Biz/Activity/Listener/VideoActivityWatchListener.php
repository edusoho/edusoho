<?php


namespace Biz\Activity\Listener;


use Biz\Task\Service\TaskService;

class VideoActivityWatchListener extends Listener
{
    /*
     * watch video, audio time, unit is second
     */
    const WATCH_TIME_STEP = 120;

    public function handle($activity, $data)
    {

        if (!$data['watchTime'] || $data['watchTime'] >= static::WATCH_TIME_STEP) {
            $watchTime = TaskService::WATCH_TIME_STEP;
        } else {
            $watchTime = $data['watchTime'];
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