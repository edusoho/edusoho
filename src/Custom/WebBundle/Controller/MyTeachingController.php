<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class MyTeachingController extends BaseController
{
	public function dashboardAction(Request $request)
	{
		$user = $this->getCurrentUser();

		if(!$user->isTeacher()) {
		    return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
		}

		return $this->render('CustomWebBundle:MyTeaching:dashboard.html.twig', array(
		));
	}
}