<?php

namespace Biz\Activity\Listener;

use Biz\Task\Service\TaskService;
use Biz\Xapi\Service\XapiService;

class LiveActivityWatchListener extends Listener
{
    public function handle($activity, $data)
    {
        //自20.4.6版本开始不记录观看时长，预计20.4.8删除
//        if (empty($data['task'])) {
//            return;
//        }
//        $watchTimeSec = $this->getTaskService()->getTimeSec('watch');
//        $data['watchTime'] = $data['watchTime'] > $watchTimeSec ? $watchTimeSec : $data['watchTime'];
//        $this->getXapiService()->watchTask($data['task']['id'], $data['watchTime']);
//        $this->getTaskService()->watchTask($data['task']['id'], $data['watchTime']);
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
