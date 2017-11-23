<?php

namespace Biz\UserLearnStatistics\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class SyncTotalJob extends AbstractJob
{
    public function execute()
    {
        $learnSetting = $this->getLearnStatisticesService()->getStatisticsSetting();
        $lastUserId = empty($learnSetting['lastUserId']) ? 0 : $learnSetting['lastUserId'];
        $users = $this->getUserService()->searchUsers(array('id_GT' => $lastUserId), array('id'=>'asc'), 0, 20000);
        
        if (empty($users)) {
            $learnSetting['syncTotalDataStatus'] = 1;
            $this->getSettingService()->set('learn_statistics', $learnSetting);
            $this->getSchedulerService()->disabledJob($this->id);
        }

        $userIds = ArrayToolkit::column($users, 'id');
        $conditions = array(
            'createdTime_GE' => 0,
            'createdTime_LT' => $learnSetting['currentTime'],
            'userIds' => $userIds,
        );
        $this->getLearnStatisticesService()->batchCreateTotalStatistics($conditions);
            
        $endUser = end($users);
        $learnSetting['lastUserId'] = $endUser['id'];
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
}