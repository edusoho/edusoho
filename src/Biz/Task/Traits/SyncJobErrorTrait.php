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
        if ($this->isTableExist('information_schema', 'innodb_locks')) {
            $locks = $this->biz['db']->fetchAll('select * from information_schema.innodb_locks');
        } else {
            $locks = $this->biz['db']->fetchAll('select * from performance_schema.data_locks');
        }
        if ($this->isTableExist('information_schema', 'innodb_lock_waits')) {
            $lockWaits = $this->biz['db']->fetchAll('select * from information_schema.innodb_lock_waits');
        } else {
            $lockWaits = $this->biz['db']->fetchAll('select * from performance_schema.data_lock_waits');
        }
        $processlist = $this->biz['db']->fetchAll('show processlist');
        $this->getLogger()->error($error->getMessage(), ['trace' => $error->getTrace(), 'trx' => $trx, 'locks' => $locks, 'lockWaits' => $lockWaits, 'process' => $processlist]);
    }

    protected function getLogger()
    {
        $logger = new Logger('SyncTaskError');
        $logger->pushHandler(new StreamHandler($this->biz['log_directory'].'/sync-task-error.log', Logger::DEBUG));

        return $logger;
    }

    protected function isTableExist($schema, $table)
    {
        $result = $this->biz['db']->fetchAssoc("select * from information_schema.tables where table_schema='{$schema}' and table_name = '{$table}'");

        return !empty($result);
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
