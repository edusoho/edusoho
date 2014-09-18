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
		return $this->render('TopxiaWebBundle:Sign:show.html.twig',array('class' => $class, 'isSignedToday' => $isSignedToday));
	}

	public function getSignedRecordsByMonthAction(Request $request, $classId, $userId)
	{
		$startDay = $request->query->get('startDay');
		$endDay = $request->query->get('endDay');
		$startDay = explode('-', $startDay);
		$endDay = explode('-', $endDay);

		$userSigns = $this->getSignService()->getSignsRecordsByMonth($userId, $classId, $startDay, $endDay);
		$result = array();
		if($userSigns) {
			foreach ($userSigns as $userSign) {
			$result['records'][] = date('d',$userSign['createdTime']);
			}
		}
		$todayRank = $this->getSignService()->getClassMemberSignStatistics($userId, $classId)['todayRank'];
		$signedNum = $this->getSignService()->getClassSignStatistics($classId)['signedNum'];

		$result['todayRank'] = $todayRank;
		$result['signedNum'] =$signedNum;
		return $this->createJsonResponse($result);
	}

	public function getSignService()
	{
		return $this->getServiceKernel()->createService('Classes.SignService');
	}

}