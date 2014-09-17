<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class SignController extends BaseController
{
	public function signAction(Request $request, $classId, $userId)
	{
		$this->getSignService()->classMemberSign($userId, $classId);
		return $this->createJsonResponse('success');
	}

	public function userInfoAction()
	{

		return $this->render('TopxiaWebBundle:Sign:show.html.twig');
	}

	public function getSignedRecordsByMonthAction(Request $request)
	{
		$user = $this->getCurrentUser();
		$startDay = $request->query->get('startDay');
		$endDay = $request->query->get('endDay');
		$startDay = explode('-', $startDay);
		$endDay = explode('-', $endDay);

		$userSigns = $this->getSignService()->getSignsRecordsByMonth($user['id'], $startDay, $endDay);
		$result = array();
		if($userSigns) {
			foreach ($userSigns as $userSign) {
			$result[] = date('d',$userSign['createdTime']);
		}
		}
		
		
		return $this->createJsonResponse($result);
	}

	public function getSignService()
	{
		return $this->getServiceKernel()->createService('Classes.SignService');
	}

}