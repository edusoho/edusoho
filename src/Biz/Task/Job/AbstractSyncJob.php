<?php

namespace Biz\Task\Job;

use Biz\Course\Dao\CourseDao;
use Biz\System\Service\LogService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class AbstractSyncJob extends AbstractJob
{
    public function execute()
    {
    }

    protected function dispatchEvent($eventName, $subject, $arguments = array())
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
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }
}
