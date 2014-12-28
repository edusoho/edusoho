<?php
namespace Custom\Service\Course\Impl;
use Custom\Service\Course\UserCourseService;
use Topxia\Service\Common\BaseService;

class UserCourseServiceImpl extends BaseService implements UserCourseService
{
	

	public function getUserCurrentlyLearnByCourseId($userId,$courseId)
	{
		$conditions['userId'] = $userId;
		$conditions['courseId'] = $courseId;
		// $conditions['status'] = 'learning';
		$resutl =  $this->getLessonLearnDao()->searchLearns($conditions,array(0=>'startTime',1=>'DESC'),0,1);
		
		if($resutl && $resutl[0]){
			return $resutl[0];
		}
		return null;
	}

	private function getLessonLearnDao ()
	{
	    return $this->createDao('Course.LessonLearnDao');
	}
	

}
