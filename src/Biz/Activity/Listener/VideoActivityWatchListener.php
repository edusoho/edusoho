<?php

namespace Biz\Activity\Listener;

use Biz\Task\Service\TaskService;
use Biz\Xapi\Service\XapiService;

class VideoActivityWatchListener extends Listener
{
    public function handle($activity, $data)
    {
        if (empty($data['task'])) {
            return;
        }
        $watchTimeSec = $this->getTaskService()->getTimeSec('watch');
        $data['watchTime'] = $data['watchTime'] > $watchTimeSec ? $watchTimeSec : $data['watchTime'];
        $this->getXapiService()->watchTask($data['task']['id'], $data['watchTime']);
        $this->getTaskService()->watchTask($data['task']['id'], $data['watchTime']);
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
