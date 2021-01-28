<?php

namespace Biz\UserLearnStatistics\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class SyncDaily extends AbstractJob
{
    public function execute()
    {
        //每天生成学习数据
        $learnSetting = $this->getLearnStatisticsService()->getStatisticsSetting();
        $cursor = $this->getSyncTime($learnSetting);
        $nextCursor = $cursor + 24 * 60 * 60;

        if (time() < $nextCursor) {
            return;
        }

        while (date('Y-m-d', $nextCursor) != date('Y-m-d', strtotime('+1 day'))) {
            $job = array(
                'name' => 'SyncDailyChildrenJob',
                'source' => 'MAIN',
                'expression' => intval(time()),
                'class' => 'Biz\UserLearnStatistics\Job\SyncDailyChildrenJob',
                'args' => array('cursor' => $cursor, 'nextCursor' => $nextCursor, 'learnStatisticsTime' => $learnSetting['currentTime']),
                'misfire_policy' => 'executing',
            );
            $this->getSchedulerService()->register($job);

            $cursor = $nextCursor;
            $nextCursor += 24 * 60 * 60;
        }

        $jobArgs['cursor'] = $cursor;
        $this->getJobDao()->update($this->id, array('args' => $jobArgs));
    }

    private function getSyncTime($learnSetting)
    {
        $jobArgs = $this->args;
        if (empty($jobArgs)) {
            $jobArgs = array();
        }

        return empty($jobArgs['cursor']) ? $learnSetting['currentTime'] : $jobArgs['cursor'];
    }

    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }

    /**
     * @return \Biz\UserLearnStatistics\Service\Impl\LearnStatisticsServiceImpl
     */
    protected function getLearnStatisticsService()
    {
        return $this->biz->service('UserLearnStatistics:LearnStatisticsService');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Scheduler:JobDao');
    }
}
