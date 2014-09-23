<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class SignController extends BaseController
{
	public function signAction(Request $request, $classId, $userId)
	{
		$this->getSignService()->classMemberSign($userId, $classId, $classId);
		return $this->createJsonResponse('success');
	}

	public function userInfoAction($class)
	{
		$user = $this->getCurrentUser();
		$isSignedToday = $this->getSignService()->isSignedToday($user['id'], $class['id']);
		return $this->render('TopxiaWebBundle:Sign:show.html.twig',array(
			'class' => $class,
			'user' => $user,
			'isSignedToday' => $isSignedToday));
	}

	public function getSignedRecordsByMonthAction(Request $request, $classId, $userId)
	{
		$startDay = $request->query->get('startDay');
		$endDay = $request->query->get('endDay');
		$startDay = explode('-', $startDay);
		$endDay = explode('-', $endDay);

		$userSigns = $this->getSignService()->getSignsRecordsByMonth($userId, $classId, $startDay, $endDay);
		$result = array();
		$result['records'] = array();
		if($userSigns) {
			foreach ($userSigns as $userSign) {
			$result['records'][] = date('d',$userSign['createdTime']);
			}
		}
		$ClassMemberSignStatistics = $this->getSignService()->getClassMemberSignStatistics($userId, $classId);
		$ClassSignStatistics = $this->getSignService()->getClassSignStatistics($classId);

		$result['todayRank'] = $ClassMemberSignStatistics['todayRank'];
		$result['signedNum'] = $ClassSignStatistics['signedNum'];
		$result['keepDays'] = $ClassMemberSignStatistics['keepDays'];
		
		return $this->createJsonResponse($result);
	}

	public function getSignService()
	{
		return $this->getServiceKernel()->createService('Classes.SignService');
	}

}