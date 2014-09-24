<?php
namespace Topxia\Service\Classes\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Classes\SignService;
use Topxia\Service\Common\ServiceEvent;

class SignServiceImpl extends BaseService implements SignService
{

    public function classMemberSign($userId, $classId)
    {
        $member = $this->getClassMemberDao()->getMemberByUserIdAndClassId($userId, $classId);
        if(empty($member)) {
            throw $this->createNotFoundException(sprintf('%s 非 %s 班成员，不能签到.', $userId, $classId), 404);
        }

        $isSignedToday = $this->isSignedToday($userId, $classId); 
        if($isSignedToday) {
            throw $this->createServiceException('今日已签到!', 403);
        }

        $sign = array();
        $sign['classId'] = $classId;
        $sign['userId'] = $userId;
        $sign['createdTime'] = time();

        $sign = $this->getClassMemberSignDao()->addClassMemberSign($sign);
        $classSignStatistics = $this->classSignedNumIncrease($classId);
        $this->refreshClassMemberSignStatistics($userId, $classId, $classSignStatistics['signedNum']);

        $this->getDispatcher()->dispatch('class.signed', new ServiceEvent());

        return $sign;
    }

    public function isSignedToday($userId, $classId)
    {
        
        $startTimeToday = strtotime(date('y-n-d 0:0:0'));
        $endTimeToday = strtotime(date('y-n-d 23:59:59'));

        $signs = $this->getClassMemberSignDao()->
            findClassMemberSignByPeriod($userId, $classId, $startTimeToday, $endTimeToday);

        return  empty($signs) ? false : true;
    }

    public function isYestodaySigned($userId, $classId)
    {
        $startTimeToday = strtotime(date('y-n-d 0:0:0', strtotime('-1 days')));
        $endTimeToday = strtotime(date('y-n-d 23:59:59', strtotime('-1 days')));

        $signs = $this->getClassMemberSignDao()->
            findClassMemberSignByPeriod($userId, $classId, $startTimeToday, $endTimeToday);
        
        return  empty($signs) ? false : true;
    }
    public function getSignRecordsByPeriod($userId, $classId, $startDay, $endDay)
    {
        $startTime = strtotime(date('y-n-d 0:0:0', strtotime($startDay)));
        $endTime = strtotime(date('y-n-d 23:59:59', strtotime($endDay)));
        $signs = $this->getClassMemberSignDao()->
            findClassMemberSignByPeriod($userId, $classId, $startTime, $endTime);

        return $signs;
    }

    public function getClassMemberSignStatistics($userId, $classId)
    {
        return $this->getClassMemberSignStatisticsDao()->getClassMemberSignStatistics($userId, $classId);
    }

    public function getClassSignStatistics($classId)
    {
        return $this->getClassSignStatisticsDao()->getClassSignStatisticsByClassId($classId);
    }

    public function refreshClassMemberSignStatistics($userId, $classId, $todayRank)
    {
        $statistics = $this->getClassMemberSignStatisticsDao()->getClassMemberSignStatistics($userId, $classId);
        if(empty($statistics)) {
            $statistics = array();
            $statistics['userId'] = $userId;
            $statistics['classId'] = $classId;
            $statistics['todayRank'] = $todayRank;
            $statistics['keepDays'] = 0;
            $statistics = $this->getClassMemberSignStatisticsDao()->addClassMemberSignStatistics($statistics);
        }

        $fields = array();
        if($this->isYestodaySigned($userId, $classId)) {
            $fields['keepDays'] = $statistics['keepDays'] + 1;
        } else {
            $fields['keepDays'] = 1;
        }
        
        $fields['todayRank'] = $todayRank;
        return $this->getClassMemberSignStatisticsDao()->updateClassMemberSignStatistics($userId, $classId, $fields);
    }

    public function refreshClassSignStatistics($classId)
    {
        $statistics = $this->getClassSignStatisticsDao()->getClassSignStatisticsByClassId($classId);
        if(empty($statistics)) {
            $statistics = array();
            $statistics['classId'] = $classId;
            $statistics['date'] = time();
            $statistics['signedNum'] = 0;
            $this->getClassSignStatisticsDao()->addClassSignStatistics($statistics);
        }
        $firstSignedDate = $statistics['date'];

        if(!$this->isSameDay($firstSignedDate)) {
            $fields = array();
            $fields['date'] = time();
            $fields['signedNum'] = 0;
            $statistics = $this->getClassSignStatisticsDao()->updateClassSignStatistics($classId, $fields);
        }   

        return $statistics;
    }

    private function classSignedNumIncrease($classId)
    {
        $statistics = $this->refreshClassSignStatistics($classId);
        $fields = array();
        $fields['signedNum'] = $statistics['signedNum'] + 1;
        return $this->getClassSignStatisticsDao()->updateClassSignStatistics($classId, $fields);
    }

    private function isSameDay($timestamp)
    {
        return date('Y', time()) > date('Y', $timestamp) 
            || date('m', time()) > date('m', $timestamp)
            || date('d', time()) > date('d', $timestamp) ? false : true;
    }

    private function getClassMemberSignDao()
    {
        return $this->createDao('Classes.ClassMemberSignDao');
    }

    private function getClassMemberSignStatisticsDao()
    {
        return $this->createDao('Classes.ClassMemberSignStatisticsDao');
    }

    private function getClassSignStatisticsDao()
    {
        return $this->createDao('Classes.ClassSignStatisticsDao');
    }
    
    private function getClassMemberDao()
    {
        return $this->createDao('Classes.ClassMemberDao');
    }
}

