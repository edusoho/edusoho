<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class SignController extends BaseController
{
	public function signAction(Request $request, $classId, $userId)
	{
		$this->getSignService()->userSign($userId, 'class_sign', $classId);
		return $this->createJsonResponse('success');
	}

	public function getSignedRecordsByPeriodAction(Request $request, $classId, $userId)
	{
		$startDay = $request->query->get('startDay');
		$endDay = $request->query->get('endDay');
	
		$userSigns = $this->getSignService()->getSignRecordsByPeriod($userId, 'class_sign', $classId, $startDay, $endDay);
		$result = array();
		$result['records'] = array();
		if($userSigns) {
			foreach ($userSigns as $userSign) {
			$result['records'][] = array(
				'day' => date('d',$userSign['createdTime']),
				'time' => date('G点m分',$userSign['createdTime']),
				'rank' => $userSign['rank']);
			}
		}
		$userSignStatistics = $this->getSignService()->getSignUserStatistics($userId, 'class_sign', $classId);
		$classSignStatistics = $this->getSignService()->getSignTargetStatistics('class_sign', $classId, date('Ymd', time()));

		$result['todayRank'] = $this->getSignService()->getTodayRank($userId, 'class_sign', $classId);
		$result['signedNum'] = $classSignStatistics['signedNum'];
		$result['keepDays'] = $userSignStatistics['keepDays'];
		
		return $this->createJsonResponse($result);
	}

	private function getSignService()
	{
		return $this->getServiceKernel()->createService('Sign.SignService');
	}

	private function getClassesService()
	{
		return $this->getServiceKernel()->createService('Classes.ClassesService');
	}

	private function getCourseService()
	{
		return $this->getServiceKernel()->createService('Course.CourseService');
	}
}