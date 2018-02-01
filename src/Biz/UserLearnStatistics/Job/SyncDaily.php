<?php

namespace Biz\UserLearnStatistics\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class SyncDaily extends AbstractJob
{
    public function execute()
    {
        //每天生成学习数据
        try {
            $learnSetting = $this->getLearnStatisticsService()->getStatisticsSetting();
            $cursor = $this->getSyncTime($learnSetting);
            $nextCursor = $cursor + 24 * 60 * 60;

            if (time() < $nextCursor) {
                return;
            }

            $this->biz['db']->beginTransaction();
            $conditions = array(
                'createdTime_GE' => $cursor,
                'createdTime_LT' => $nextCursor,
                'event_EQ' => 'doing',
            );

            if ($cursor == $learnSetting['currentTime']) {
                //当天升级的数据为了准确性，不统计加入退出课程数
                $conditions['skipSyncCourseSetNum'] = true;
            }
            $this->getLearnStatisticsService()->batchCreateDailyStatistics($conditions);

            $jobArgs['cursor'] = $nextCursor;
            $this->getJobDao()->update($this->id, array('args' => $jobArgs));
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
        }
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
