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
	public function getSignService()
	{
		return $this->getServiceKernel()->createService('Classes.SignService');
	}
}