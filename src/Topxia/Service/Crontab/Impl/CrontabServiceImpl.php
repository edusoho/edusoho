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

        return $this->getJobDao()->addJob($job);
    }

    public function executeJob($id)
    {
        $job = $this->getJob($id);

        $this->getJobDao()->updateJob($job['id'], array('executing' => 1));

        $jobInstance = new $job['jobClass']();
        $jobInstance->execute($job['jobParams']);

        if ($job['cycle'] == 'once') {
            $this->getJobDao()->deleteJob($job['id']);
        }
    }

    public function deleteJob($id)
    {
        return $this->getJobDao()->deleteJob($id);
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
            $this->getLogService()->info('crontab', 'job_start', "定时任务(#{$job['id']})执行结束！", $job);
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

}
