<?php

namespace Biz\UserLearnStatistics\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class SyncTotalJob extends AbstractJob
{
    public function execute()
    {
        try {
            $this->biz['db']->beginTransaction();
            $cursor = $this->getSyncTime();
            $nextCursor = $cursor - 24*60*60;
            /*
                skipSyncCourseSetNum，跳过同步 加入课程数和退出课程数
                退出课程数： 退出课程的最后一个计划
                加入班级数： 加入的课程第一个计划
            */
            $conditions = array(
                'createdTime_GE' => $nextCursor,
                'createdTime_LT' => $cursor,
                'skipSyncCourseSetNum' => true,
            );
            $this->getLearnStatisticesService()->batchCreateDailyStatistics($conditions);

            $jobArgs['cursor'] = $nextCursor;
            $this->getJobDao()->update($this->id, array('args' => $jobArgs));
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
        }

    }

    private function getSyncTime()
    {
        $learnSetting = $this->getLearnStatisticesService()->getStatisticsSetting();
        $jobArgs = $this->args;
        if (empty($jobArgs)) {
            $jobArgs = array();
        }

        return empty($jobArgs['cursor']) ? $learnSetting['currentTime'] : $jobArgs['cursor'];
    }

    private function setTotalDataStatus($learnSetting)
    {
        $learnSetting['syncTotalDataStatus'] = 1;

        $this->getSettingService()->set('learn_statistics', $learnSetting);
    }

    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }

    protected function getLearnStatisticesService()
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