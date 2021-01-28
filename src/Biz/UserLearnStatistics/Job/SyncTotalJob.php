<?php

namespace Biz\UserLearnStatistics\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class SyncTotalJob extends AbstractJob
{
    public function execute()
    {
        //生成用户总量学习数据
        try {
            $lastUserId = $this->getLastUserId();
            $magic = $this->getSettingService()->get('magic');
            $limit = empty($magic['user_sync_learn_total_data_limit']) ? 30 : $magic['user_sync_learn_total_data_limit'];
            $users = $this->getUserService()->searchUsers(array('id_GT' => $lastUserId), array('id' => 'asc'), 0, $limit);
            $learnSetting = $this->getLearnStatisticsService()->getStatisticsSetting();

            if (empty($users) || !empty($learnSetting['syncTotalDataStatus'])) {
                $this->setTotalDataStatus($learnSetting);
                $this->getSchedulerService()->disabledJob($this->id);

                return;
            }
            $this->biz['db']->beginTransaction();
            $userIds = ArrayToolkit::column($users, 'id');
            /*
                skipSyncCourseSetNum，跳过同步 加入课程数和退出课程数
                退出课程数： 退出课程的最后一个计划
                加入班级数： 加入的课程第一个计划
                历史数据统计不了，故，user_learn_statistics_total初始化时，这两个数据不同步
            */
            $conditions = array(
                'createdTime_GE' => 0,
                'createdTime_LT' => $learnSetting['currentTime'],
                'userIds' => $userIds,
                'skipSyncCourseSetNum' => true,
                'event_EQ' => 'doing',
            );
            $this->getLearnStatisticsService()->batchCreateTotalStatistics($conditions);
            $endUser = end($users);
            $jobArgs['lastUserId'] = $endUser['id'];
            $this->getJobDao()->update($this->id, array('args' => $jobArgs));
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
        }
    }

    private function getLastUserId()
    {
        $jobArgs = $this->args;
        if (empty($jobArgs)) {
            $jobArgs = array();
        }

        return empty($jobArgs['lastUserId']) ? 0 : $jobArgs['lastUserId'];
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
