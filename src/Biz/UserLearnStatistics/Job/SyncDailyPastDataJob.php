<?php

namespace Biz\UserLearnStatistics\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class SyncDailyPastDataJob extends AbstractJob
{
    public function execute()
    {
        //生成一年内的用户学习数据
        try {
            $learnSetting = $this->getLearnStatisticsService()->getStatisticsSetting();
            $cursor = $this->getSyncTime($learnSetting);

            if (($learnSetting['currentTime'] - $cursor) > $learnSetting['timespan']) {
                $this->getSchedulerService()->disabledJob($this->id);

                return;
            }

            $this->biz['db']->beginTransaction();
            $nextCursor = $cursor - 24 * 60 * 60;
            /*
                skipSyncCourseSetNum，跳过同步 加入课程数和退出课程数
                退出课程数： 退出课程的最后一个计划
                加入班级数： 加入的课程第一个计划
            */
            $conditions = array(
                'createdTime_GE' => $nextCursor,
                'createdTime_LT' => $cursor,
                'skipSyncCourseSetNum' => true,
                'event_EQ' => 'doing',
            );
            $this->getLearnStatisticsService()->batchCreatePastDailyStatistics($conditions);

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

    protected function getJobDao()
    {
        return $this->biz->dao('Scheduler:JobDao');
    }
}
