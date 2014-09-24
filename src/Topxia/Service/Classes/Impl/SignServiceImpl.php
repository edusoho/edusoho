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

        $ClassMemberSign = array();
        $ClassMemberSign['classId'] = $classId;
        $ClassMemberSign['userId'] = $userId;
        $ClassMemberSign['createdTime'] = time();
        $ClassMemberSignDao = $this->getClassMemberSignDao();

        $ClassMemberSignDao->getConnection()->beginTransaction();
        try {
            $ClassMemberSign = $ClassMemberSignDao->addClassMemberSign($ClassMemberSign);
            $classSignStatistics = $this->classSignedNumIncrease($classId);
            $this->refreshClassMemberSignStatistics($userId, $classId, $classSignStatistics['signedNum']);

            $this->getDispatcher()->dispatch('user.signed', new ServiceEvent());
            //commit if no error
            $ClassMemberSignDao->getConnection()->commit();

        } catch(\Exception $e) {
            //roll back if has error
            $ClassMemberSignDao->getConnection()->rollback();
            throw $e; 
        }

        return $ClassMemberSign;
    }

    public function isSignedToday($userId, $classId)
    {
        $startTimeToday = mktime(0, 0, 0, date('m', time()), date('d', time()), date('Y', time()) );
        $endTimeToday = mktime(23, 59, 59, date('m', time()), date('d', time()), date('Y', time()) );

        $ClassMemberSigns = $this->getClassMemberSignDao()->
            findClassMemberSignByPeriod($userId, $classId, $startTimeToday, $endTimeToday);
        if(!empty($ClassMemberSigns)) {
            return true;
        } else {
            return false;
        }   
    }

    public function isYestodaySigned($userId, $classId)
    {
        $startTimeToday = mktime(0, 0, 0, date('m', strtotime('-1 days')), date('d', strtotime('-1 days')), date('Y', strtotime('-1 days')) );
        $endTimeToday = mktime(23, 59, 59, date('m', strtotime('-1 days')), date('d', strtotime('-1 days')), date('Y', strtotime('-1 days')) );

        $ClassMemberSigns = $this->getClassMemberSignDao()->
            findClassMemberSignByPeriod($userId, $classId, $startTimeToday, $endTimeToday);
        if(!empty($ClassMemberSigns)) {
            return true;
        } else {
            return false;
        }
    }
    public function getSignsRecordsByMonth($userId, $classId, $startDay, $endDay)
    {
        $startTime = mktime(0, 0, 0, $startDay[0], $startDay[1], $startDay[2]);
        $endTime = mktime(23, 59, 59, $endDay[0], $endDay[1], $endDay[2]);
        $ClassMemberSigns = $this->getClassMemberSignDao()->
            findClassMemberSignByPeriod($userId, $classId, $startTime, $endTime);

        return $ClassMemberSigns;
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
        $ClassMemberSignStatisticsDao = $this->getClassMemberSignStatisticsDao();
        $ClassMemberSignStatistics = $ClassMemberSignStatisticsDao->getClassMemberSignStatistics($userId, $classId);
        if(empty($ClassMemberSignStatistics)) {
            $ClassMemberSignStatistics = array();
            $ClassMemberSignStatistics['userId'] = $userId;
            $ClassMemberSignStatistics['classId'] = $classId;
            $ClassMemberSignStatistics['todayRank'] = $todayRank;
            $ClassMemberSignStatistics['keepDays'] = 0;
            $ClassMemberSignStatistics = $ClassMemberSignStatisticsDao->addClassMemberSignStatistics($ClassMemberSignStatistics);
        }

        $fields = array();
        if($this->isYestodaySigned($userId, $classId)) {
            $fields['keepDays'] = $ClassMemberSignStatistics['keepDays'] + 1;
        } else {
            $fields['keepDays'] = 1;
        }
        
        $fields['todayRank'] = $todayRank;
        return $ClassMemberSignStatisticsDao->updateClassMemberSignStatistics($userId, $classId, $fields);
    }

    public function classSignedNumIncrease($classId)
    {
        $classSignStatistics = $this->refreshClassSignStatistics($classId);
        $fields = array();
        $fields['signedNum'] = $classSignStatistics['signedNum'] + 1;
        return $this->getClassSignStatisticsDao()->updateClassSignStatistics($classId, $fields);
    }

    public function refreshClassSignStatistics($classId)
    {
        $classSignStatisticsDao = $this->getClassSignStatisticsDao();
        $classSignStatistics = $classSignStatisticsDao->getClassSignStatisticsByClassId($classId);
        if(empty($classSignStatistics)) {
            $classSignStatistics = array();
            $classSignStatistics['classId'] = $classId;
            $classSignStatistics['date'] = time();
            $classSignStatistics['signedNum'] = 0;
            $classSignStatisticsDao->addClassSignStatistics($classSignStatistics);
        }
        $firstSignedDate = $classSignStatistics['date'];
        if(date('Y', time()) > date('Y', $firstSignedDate) 
            || date('m', time()) > date('m', $firstSignedDate)
            || date('d', time()) > date('d', $firstSignedDate)) {
            $fields = array();
            $fields['date'] = time();
            $fields['signedNum'] = 0;
            $classSignStatistics = $classSignStatisticsDao->updateClassSignStatistics($classId, $fields);
        }   

        return $classSignStatistics;
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

