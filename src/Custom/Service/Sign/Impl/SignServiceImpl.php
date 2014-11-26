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

        if(isset($set['daySign']) && $set['daySign']>0 ){
            $this->getCashService()->reWard($set['daySign'],"每日签到奖励",$user['id']);
        }

        if($vip){

            $level=$this->getLevelService()->getLevel($vip['levelId']);

            if($level && $this->getVipService()->checkUserInMemberLevel($user['id'],$vip['levelId'])=="ok"){
                
                if($statistics['keepDays']%7 == 0 && $level['signReward'] > 0){

                    $this->getCashService()->reWard($level['signReward'],"连续签到奖励",$user['id']); 

                }

                if($level['signInCards']>0){

                    $card=$this->getSignCardDao()->getSignCardByUserId($user['id']);

                    if(empty($card)){

                        $signCard=array(
                            'userId'=>$user['id'],
                            'cardNum'=>$level['signInCards'],
                            'useTime'=>time(),
                            );
                        $card=$this->getSignCardDao()->addSignCard($signCard);
                    }

                    $now=date('Y-m',time());

                    if(strtotime($now)>$card['useTime']){

                        $this->getSignCardDao()->updateSignCard($card['id'],array(
                            'cardNum'=>$level['signInCards']));
                    }
                }

            }
        }

        return $sign;
    }

    public function getSignCardByUserId($userId)
    {
        return $this->getSignCardDao()->getSignCardByUserId($userId);
    }

    public function repairSign($userId, $targetType, $targetId,$date)
    {
        $user = $this->getUserService()->getUser($userId);

        $day=strtotime($date);

        if(date('Y-m',$day) != date('Y-m',time()) || date('Y-m-d',$day) > date('Y-m-d',time())){

            throw $this->createServiceException('只能补签本月!', 403);

        }

        $startTimeToday = strtotime(date('Y-m-d',$day).' 0:0:0');
        $endTimeToday = strtotime(date('Y-m-d',$day).' 23:59:59');
        $signs = $this->getSignUserLogDao()->
            findSignLogByPeriod($userId, $targetType, $targetId, $startTimeToday, $endTimeToday);

        if($signs){

            throw $this->createServiceException('今日已签到!', 403);
        }

        $vip=$this->getVipService()->getMemberByUserId($user['id']);

        if($vip){

            $level=$this->getLevelService()->getLevel($vip['levelId']);

            if($level && $this->getVipService()->checkUserInMemberLevel($user['id'],$vip['levelId'])=="ok"){

                if($level['signInCards']>0){

                    $card=$this->getSignCardDao()->getSignCardByUserId($user['id']);

                    if(empty($card)){

                        $signCard=array(
                            'userId'=>$user['id'],
                            'cardNum'=>$level['signInCards'],
                            'useTime'=>time(),
                            );
                        $card=$this->getSignCardDao()->addSignCard($signCard);
                    }

                    $now=date('Y-m',time());

                    if(strtotime($now)>$card['useTime']){

                        $this->getSignCardDao()->updateSignCard($card['id'],array(
                            'cardNum'=>$level['signInCards']));
                    }
                }

            }
        }

        $card=$this->getSignCardDao()->getSignCardByUserId($user['id']);

        if(empty($card) || $card['cardNum'] == 0 ){

            throw $this->createServiceException('补签卡不足!', 403);
        }

        $this->getSignCardDao()->waveCrad($card['id'],1);
        $this->getSignCardDao()->updateSignCard($card['id'],array('useTime'=>time()));

        $sign = array();
        $sign['userId'] = $userId;
        $sign['targetId'] = $targetId;
        $sign['targetType'] = $targetType;
        $sign['createdTime'] = strtotime(date('Y-m-d',$day).'0:0:30');

        $sign = $this->getSignUserLogDao()->addSignLog($sign);
        $statistics = $this->targetSignedNumIncrease($targetType, $targetId, date('Ymd', $day));

        $statistics=$this->repairhKeepDays($userId, $targetType, $targetId,$day);

        $set=$this->getSettingService()->get('group',array());

        if(isset($set['daySign']) && $set['daySign']>0 ){
            $this->getCashService()->reWard($set['daySign'],"每日签到奖励",$user['id']);
        }

        if($vip){

            $level=$this->getLevelService()->getLevel($vip['levelId']);

            if($level && $this->getVipService()->checkUserInMemberLevel($user['id'],$vip['levelId'])=="ok"){
                
                if($statistics['keepDays']%7 == 0 && $level['signReward'] > 0 ){

                    $this->getCashService()->reWard($level['signReward'],"连续签到奖励",$user['id']);
       
                }

            }
        }

        $this->getDispatcher()->dispatch('group.repairSign', new ServiceEvent());
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

    public function isSignedAll($userId, $targetType, $targetId,$time)
    {
        $startTimeToday = $time;
        $endTimeToday = strtotime(date('y-n-d 23:59:59'));

        $signs = $this->getSignUserLogDao()->
            findSignLogByPeriod($userId, $targetType, $targetId, $startTimeToday, $endTimeToday);
        
        $day=date('d',$time);
        $now=date('d',time());

        $count=$now-$day+1;
       
        if($count == count($signs)){

            return true;
        }
        return false ;
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

    private function repairhKeepDays($userId, $targetType, $targetId,$time)
    {
        $statistics = $this->getSignUserStatisticsDao()
            ->getStatistics($userId, $targetType, $targetId);
        if($statistics) {
            if($this->isSignedAll($userId, $targetType, $targetId,$time)){

                $statistics=$this->getSignUserStatisticsDao()
                ->updateStatistics($statistics['id'], array('keepDays' => $statistics['keepDays'] + 1));
          
                $this->isYestodaySignedRepair($userId, $targetType, $targetId,$time,$statistics);

            }

        } else {
            $statistics['userId'] = $userId;
            $statistics['targetType'] = $targetType;
            $statistics['targetId'] = $targetId;
            $statistics['keepDays'] = 1;
            $statistics['createdTime'] = $time;
            $statistics = $this->getSignUserStatisticsDao()
                ->addStatistics($statistics);
        }

        $statistics = $this->getSignUserStatisticsDao()
            ->getStatistics($userId, $targetType, $targetId);

        return $statistics;
    }

    private function isYestodaySignedRepair($userId, $targetType, $targetId,$time,$statistics)
    {
        $startTimeToday = strtotime(date('y-n-d 0:0:0', strtotime('-1 days',$time)));
        $endTimeToday = strtotime(date('y-n-d 23:59:59', strtotime('-1 days',$time)));

        $signs = $this->getSignUserLogDao()->
            findSignLogByPeriod($userId, $targetType, $targetId, $startTimeToday, $endTimeToday);
    
        if(!empty($signs)){

            $statistics=$this->getSignUserStatisticsDao()
                ->updateStatistics($statistics['id'], array('keepDays' => $statistics['keepDays'] + 1));
            
            $time=$time-3600*24;
            $this->isYestodaySignedRepair($userId, $targetType, $targetId,$time,$statistics);
        }

        return ;
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

    private function getSignCardDao()
    {
        return $this->createDao('Custom:Sign.SignCardDao');
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

