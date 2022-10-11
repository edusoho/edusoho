<?php

namespace Biz\Task\Job;

use Biz\Course\Dao\CourseDao;
use Biz\Lock;
use Biz\System\Service\LogService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;

class AbstractSyncJob extends AbstractJob
{
    private $lock;

    public function execute()
    {
    }

    protected function innodbTrxLog(\Exception $error)
    {
        $trx = $this->biz['db']->fetchAll('select * from information_schema.innodb_trx where TIME_TO_SEC(timediff(now(),trx_started))>30');
        $processlist = $this->biz['db']->fetchAll('show processlist');
        $this->getLogger()->error($error->getMessage(),['trace'=>$error->getTrace(),'trx'=> $trx, 'process' => $processlist]);
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

    protected function getLock()
    {
        if (!$this->lock) {
            $this->lock = new Lock($this->biz);
        }

        return $this->lock;
    }

    protected function getLogger()
    {
        $logger = new Logger('SyncTaskError');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/sync-task-error.log', Logger::DEBUG));
        return $logger;
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

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
