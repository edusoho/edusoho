<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyTaskController extends BaseController
{
	public function indexAction(Request $request){
		$user = $this->getCurrentUser();
		$conditions=array(
			'userId' => $user['id']
			);
		$tasks = $this->getTaskService()->searchTasks($conditions,array('taskStartTime','DESC'),0,9999);
		foreach($task as $key =>$tasks){
			$task['createTime']=$task['createTime'].date(”Y-m-d H:i:s”,time());
		}
		$jsontasks=json_encode($tasks);
		//var_dump($jsontasks);
		//exit();
		if($user->isTeacher()){
		return $this->render('TopxiaWebBundle:MyTask:index.html.twig', array(
            'user'=>$user,
            ));
		}
		
	}
	protected function getTaskService()
	{
		return $this->getServiceKernel()->createService('Task.TaskService');
	}
}