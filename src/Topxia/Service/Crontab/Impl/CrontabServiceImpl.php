<?php
namespace Topxia\Service\Crontab\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Crontab\CrontabService;

class CrontabServiceImpl extends BaseService implements CrontabService
{

    public function getJob($id)
    {
        return $this->getJobDao()->getJob($id);
    }

    public function createJob($job)
    {
        $user = $this->getCurrentUser();

        if ($job['cycle'] == 'once') {
            $job['nextExcutedTime'] = $job['time'];
            unset($job['time']);
        }

        $job['creatorId'] = $user['id'];
        $job['createdTime'] = time();

        $job = $this->getJobDao()->addJob($job);

        $this->refreshNextExecutedTime();

        return $job;
    }

    public function executeJob($id)
    {
        try {

            // 开始执行job的时候，设置next_executed_time为0，防止更多的请求进来执行
            $this->getSettingService()->set('crontab_next_executed_time', 0);

            $this->getJobDao()->getConnection()->beginTransaction();
            
            // 加锁
            $job = $this->getJob($id, true);
            // 并发的时候，一旦有多个请求进来执行同个任务，阻止第２个起的请求执行任务
            if (empty($job) || $job['executing']) {
                $this->getLogService()->error('crontab', 'execute', "任务(#{$job['id']})已经完成或者在执行");
                $this->getJobDao()->getConnection()->commit();
                return;
            }

            $this->getJobDao()->updateJob($job['id'], array('executing' => 1));

            $jobInstance = new $job['jobClass']();
            $jobInstance->execute($job['jobParams']);

            if ($job['cycle'] == 'once') {
                $this->getJobDao()->deleteJob($job['id']);
            }

            $this->getJobDao()->getConnection()->commit();

            $this->refreshNextExecutedTime();

        } catch(Exception $e) {
            $this->getJobDao()->getConnection()->rollback();
            $message = $e->getMessage();
            $this->getLogService()->error('crontab', 'execute', "执行任务(#{$job['id']})失败: {$message}");
            $this->refreshNextExecutedTime();
        }

    }

    public function deleteJob($id)
    {
        $deleted = $this->getJobDao()->deleteJob($id);
        $this->refreshNextExecutedTime();
        return $deleted;
    }

    public function scheduleJobs()
    {
        $conditions = array(
            'executing' => 0,
            'nextExcutedTime' => time(),
        );
        $job = $this->getJobDao()->searchJobs($conditions, array('nextExcutedTime', 'ASC'), 0, 1);
        if (!empty($job)) {
            $job = $job[0];
            $this->getLogService()->info('crontab', 'job_start', "定时任务(#{$job['id']})开始执行！", $job);
            $this->executeJob($job['id']);
            $this->getLogService()->info('crontab', 'job_end', "定时任务(#{$job['id']})执行结束！", $job);
        }
    }

    private function refreshNextExecutedTime()
    {
        $conditions = array(
            'executing' => 0,
        );

        $job = $this->getJobDao()->searchJobs($conditions, array('nextExcutedTime', 'ASC'), 0, 1);
        if (empty($job)) {
            $this->getSettingService()->set('crontab_next_executed_time', 0);
        } else {
            $this->getSettingService()->set('crontab_next_executed_time', $job[0]['nextExcutedTime']);
        }

    }

    protected function getJobDao()
    {
        return $this->createDao('Crontab.JobDao');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

    private function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

}
