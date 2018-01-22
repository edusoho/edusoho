<?php

namespace Biz\Activity\Listener;

use Biz\Task\Service\TaskService;
use Biz\Xapi\Service\XapiService;

class VideoActivityWatchListener extends Listener
{
    public function handle($activity, $data)
    {
        $magicSetting = $this->getSettingService()->get('magic');
        $watchTimeSec = isset($magicSetting['watch_time_sec']) && !empty($magicSetting['watch_time_sec']) ? $magicSetting['watch_time_sec'] : TaskService::WATCH_TIME_STEP;
        if (!empty($data['watchTime']) && $data['watchTime'] <= $watchTimeSec) {
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

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
