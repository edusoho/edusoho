<?php

namespace Biz\Task\Job;

use Biz\System\Service\LogService;
use Biz\Task\Service\TaskService;
use Biz\Task\Traits\SyncJobErrorTrait;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class AbstractSyncJob extends AbstractJob
{
    use SyncJobErrorTrait;

    public function execute()
    {
    }

    protected function dispatchEvent($eventName, $subject, $arguments = [])
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        return $this->biz['dispatcher']->dispatch($eventName, $event);
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }
}
