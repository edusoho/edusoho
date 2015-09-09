<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyTaskController extends BaseController
{
	public function indexAction(Request $request){
		$user = $this->getCurrentUser();
		$condition=array(
			'userId' => $user['id']
			);
		$tasks = $this->getTaskService()->serchTasks($condition,,0,9999)
		if($user->isTeacher())
		return $this->render('TopxiaWebBundle:MyTask:index.html.twig', array(
            'user'=>$user,
            ));
		
	}
	protected function getTaskService()
	{
		return $this->getServiceKernel()->createService('Task.TaskService');
	}
}