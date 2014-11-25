<?php
namespace Custom\Service\Sign\Impl;

use Topxia\Service\Common\BaseService;
use Custom\Service\Sign\SignService;
use Topxia\Service\Common\ServiceEvent;

class SignServiceImpl extends BaseService implements SignService
{

    public function userSign($userId, $targetType, $targetId)
    {
        $user = $this->getUserService()->getUser($userId);
        if(empty($user)) {
            throw $this->createNotFoundException(sprintf('用户不存在.'), 404);
        }

        $vip=$this->getVipService()->getMemberByUserId($user['id']);

        $isSignedToday = $this->isSignedToday($userId, $targetType, $targetId); 
        if($isSignedToday) {
            throw $this->createServiceException('今日已签到!', 403);
        }

        $sign = array();
        $sign['userId'] = $userId;
        $sign['targetId'] = $targetId;
        $sign['targetType'] = $targetType;
        $sign['createdTime'] = time();

        $sign = $this->getSignUserLogDao()->addSignLog($sign);
        $statistics = $this->targetSignedNumIncrease($targetType, $targetId, date('Ymd', time()));
        $this->getSignUserLogDao()
            ->updateSignLog($sign['id'], array('rank' => $statistics['signedNum']));
        $statistics=$this->refreshKeepDays($userId, $targetType, $targetId);

        $this->getDispatcher()->dispatch('group.signed', new ServiceEvent());

        $set=$this->getSettingService()->get('group',array());

        if($set){
            $this->getCashService()->reWard($set['daySign'],"每日签到奖励",$user['id']);
        }


        if($vip){

            $level=$this->getLevelService()->getLevel($vip['levelId']);

            if($level && $this->getVipService()->checkUserInMemberLevel($user['id'],$vip['levelId'])=="ok"){
                
                if($statistics['keepDays']%7 == 0)
                $this->getCashService()->reWard($level['signReward'],"连续签到奖励",$user['id']);
       
            }
        }

        return $sign;
    }

    public function isSignedToday($userId, $targetType, $targetId)
    {
        
        $startTimeToday = strtotime(date('y-n-d 0:0:0'));
        $endTimeToday = strtotime(date('y-n-d 23:59:59'));

        $signs = $this->getSignUserLogDao()->
            findSignLogByPeriod($userId, $targetType, $targetId, $startTimeToday, $endTimeToday);

        return  empty($signs) ? false : true;
    }

    public function isYestodaySigned($userId, $targetType, $targetId)
    {
        $startTimeToday = strtotime(date('y-n-d 0:0:0', strtotime('-1 days')));
        $endTimeToday = strtotime(date('y-n-d 23:59:59', strtotime('-1 days')));

        $signs = $this->getSignUserLogDao()->
            findSignLogByPeriod($userId, $targetType, $targetId, $startTimeToday, $endTimeToday);
        
        return  empty($signs) ? false : true;
    }

    public function getSignRecordsByPeriod($userId, $targetType, $targetId, $startDay, $endDay)
    {
        $startTime = strtotime(date('y-n-d 0:0:0', strtotime($startDay)));
        $endTime = strtotime(date('y-n-d 23:59:59', strtotime($endDay)));
        $signs = $this->getSignUserLogDao()->
            findSignLogByPeriod($userId, $targetType, $targetId, $startTime, $endTime);

        return $signs;
    }

    public function getSignUserStatistics($userId, $targetType, $targetId)
    {
        return $this->getSignUserStatisticsDao()->getStatistics($userId, $targetType, $targetId);
    }

    public function getSignTargetStatistics($targetType, $targetId, $date)
    {
        return $this->getSignTargetStatisticsDao()->getStatistics($targetType, $targetId, $date);
    }

    public function getTodayRank($userId, $targetType, $targetId)
    {
        $todaySign =$this->getSignRecordsByPeriod($userId, $targetType, $targetId, date('y-n-d'), date('y-n-d'));
        return $todaySign ? $todaySign['0']['rank'] : -1;
    }

    private function refreshKeepDays($userId, $targetType, $targetId)
    {
        $statistics = $this->getSignUserStatisticsDao()
            ->getStatistics($userId, $targetType, $targetId);
        if($statistics) {
            $statistics = $this->isYestodaySigned($userId, $targetType, $targetId) ?
                $this->getSignUserStatisticsDao()
                    ->updateStatistics($statistics['id'], array('keepDays' => $statistics['keepDays'] + 1)) :
                $this->getSignUserStatisticsDao()
                    ->updateStatistics($statistics['id'], array('keepDays' => 1));
        } else {
            $statistics['userId'] = $userId;
            $statistics['targetType'] = $targetType;
            $statistics['targetId'] = $targetId;
            $statistics['keepDays'] = 1;
            $statistics['createdTime'] = time();
            $statistics = $this->getSignUserStatisticsDao()
                ->addStatistics($statistics);
        }
        return $statistics;
    }

    private function targetSignedNumIncrease($targetType, $targetId, $date)
    {
        $statistics = $this->getSignTargetStatisticsDao()->getStatistics($targetType, $targetId, $date);
        if($statistics) {
            $fields = array();
            $fields['signedNum'] = $statistics['signedNum'] + 1;
            $statistics = $this->getSignTargetStatisticsDao()->updateStatistics($statistics['id'], $fields);
        } else {
            $statistics['targetType'] = $targetType;
            $statistics['targetId'] = $targetId;
            $statistics['signedNum'] = 1;
            $statistics['date'] = $date;
            $statistics['createdTime'] = time();
            $statistics = $this->getSignTargetStatisticsDao()->addStatistics($statistics);
        }

        return $statistics;
    }

    private function getSignUserLogDao()
    {
        return $this->createDao('Custom:Sign.SignUserLogDao');
    }

    private function getSignUserStatisticsDao()
    {
        return $this->createDao('Custom:Sign.SignUserStatisticsDao');
    }

    private function getSignTargetStatisticsDao()
    {
        return $this->createDao('Custom:Sign.SignTargetStatisticsDao');
    }
    
    private function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getCashService(){
      
        return $this->createService('Coin:Cash.CashService');
    }

    protected function getVipService()
    {
        return $this->createService('Vip:Vip.VipService');
    } 

    protected function getLevelService()
    {
        return $this->createService('Vip:Vip.LevelService');
    }
}

