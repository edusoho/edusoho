<?php
namespace Topxia\Service\Classes\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Classes\SignService;

class SignServiceImpl extends BaseService implements SignService
{

    public function classMemberSign($userId, $classId)
    {
    	$member = $this->getClassMemberDao()->getMemberByUserIdAndClassId($userId, $classId);
    	if(empty($member)) {
    		throw $this->createServiceException(sprintf('%s 非 %s 班成员，不能签到.',$userId,$classId));
    	}

    	$isSignedToday = $this->isSignedToday($userId); 
    	if(!$isSignedToday) {
    		throw $this->createServiceException('今日已签到!');
    	}

    	$userSign = array();
    	$userSign['userId'] = $userId;
    	$userSign['createdTime'] = time();
    	$userSignDao = $this->getUserSignDao();

    	$userSignDao->getConnection()->beginTransaction();
    	try {
			$userSign = $userSignDao->addUserSign($userSign);
			$classSignRelated = $this->classSignedNumIncrease($classId);
			$this->refreshUserSignRelated($userId, $classSignRelated['todayRank']);
			//commit if no error
			$userSignDao->getConnection()->commit();

		} catch(\Exception $e) {
			//roll back if has error
			$userSignDao->getConnection()->rollback();
			throw $e; 
		}

		return $userSign;
    }

    public function isSignedToday($userId)
    {
    	$startTimeToday = mktime(0, 0, 0, date('m', time()), date('d', time()), date('Y', time()) );
    	$endTimeToday = mktime(23, 59, 59, date('m', time()), date('d', time()), date('Y', time()) );

    	$userSigns = $this->getUserSignDao()->
    		findUserSignByUserIdAndPeriod($userId, $startTimeToday, $endTimeToday);
    	if(!empty($userSigns)) {
    		return true;
    	} else {
    		return false;
    	}	
    }

    public function refreshUserSignRelated($userId, $todayRank)
    {
    	$userSignRelatedDao = $this->getUserSignRelatedDao();
    	$userSignRelated = $userSignRelatedDao->getUserSignRelatedByUserId($userId);
    	if(empty($userSignRelated)) {
    		$userSignRelated = array();
    		$userSignRelated['userId'] = $userId;
    		$userSignRelated['todayRank'] = $todayRank;
    		$userSignRelated['keepDays'] = 0;
    		$userSignRelated = $userSignRelatedDao->addUserSignRelated($userSignRelated);
    	}

    	$fields = array();
    	$fields['keepDays'] = $userSignRelated['keepDays'] + 1;
    	$fields['todayRank'] = $todayRank;
    	return $userSignRelatedDao->updateUserSignRelated($userId, $fields);
    }

    public function classSignedNumIncrease($classId)
    {
    	$classSignRelated = $this->refreshClassSignRelated($classId);
    	$fields = array();
    	$fields['signedNum'] = $classSignRelated['signedNum'] + 1;
    	return $this->getClassSignRelatedDao()->updateClassSignRelated($classId, $fields);
    }

    public function refreshClassSignRelated($classId)
    {
    	$classSignRelatedDao = $this->getClassSignRelatedDao();
    	$classSignRelated = $classSignRelatedDao->getUserClassSignRelated($classId);
    	if(empty($classSignRelated)) {
    		$classSignRelated = array();
    		$classSignRelated['classId'] = $classId;
    		$classSignRelated['date'] = time();
    		$classSignRelated['signedNum'] = 0;
    		$classSignRelatedDao->addClassSignRelated($classSignRelated);
    	}
    	$firstSignedDate = $classSignRelated['date'];
    	if(date('Y', time()) > date('Y', $firstSignedDate) 
    		|| date('m', time()) > date('m', $firstSignedDate)
    		|| date('d', time()) > date('d', $firstSignedDate)) {
    		$fields = array();
    		$fields['date'] = time();
    		$fields['signedNum'] = 0;
    		$classSignRelated = $classSignRelatedDao->updateClassSignRelated($classId, $fields);
    	}	

    	return $classSignRelated;
    }

    private function getUserSignDao()
    {
        return $this->createDao('Classes.UserSignDao');
    }

    private function getUserSignRelatedDao()
    {
        return $this->createDao('Classes.UserSignRelatedDao');
    }

    private function getClassSignRelatedDao()
    {
        return $this->createDao('Classes.ClassSignRelatedDao');
    }
    
    private function getClassMemberDao()
    {
        return $this->createDao('Classes.ClassMemberDao');
    }
}