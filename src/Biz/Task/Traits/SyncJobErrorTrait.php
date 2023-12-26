<?php

namespace Biz\Task\Traits;

use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

trait SyncJobErrorTrait
{
    protected function innodbTrxLog(\Exception $error)
    {
        $trx = $this->biz['db']->fetchAll('select * from information_schema.innodb_trx where TIME_TO_SEC(timediff(now(), trx_started)) > 30');
        $locks = $this->biz['db']->fetchAll('select * from information_schema.innodb_locks');
        $lockWaits = $this->biz['db']->fetchAll('select * from information_schema.innodb_lock_waits');
        $processlist = $this->biz['db']->fetchAll('show processlist');
        $this->getLogger()->error($error->getMessage(), ['trace' => $error->getTrace(), 'trx' => $trx, 'locks' => $locks, 'lockWaits' => $lockWaits, 'process' => $processlist]);
    }

    protected function getLogger()
    {
        $logger = new Logger('SyncTaskError');
        $logger->pushHandler(new StreamHandler($this->biz['log_directory'].'/sync-task-error.log', Logger::DEBUG));

        return $logger;
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
