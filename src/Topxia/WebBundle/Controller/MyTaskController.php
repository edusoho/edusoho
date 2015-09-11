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
			'userId' => $user['id'],
			'status' => 'active'
			);
		$tasks = $this->getTaskService()->searchTasks($conditions,array('taskStartTime','DESC'),0,9999);
		$tasksevents=array(array(
			));
		if($tasks){
			foreach($tasks as $key => $task){
				$tasksevents[$key]['title'] = $task['title'];
				$tasksevents[$key]['start'] = date("Y-m-d H:i:s",$task['taskStartTime']);
				$tasksevents[$key]['end'] = date("Y-m-d H:i:s",$task['taskEndTime']);
				switch($task['targetType']){
					case  'testpaper':
					$tasksevents[$key]['url'] = $this->generateUrl('course_manage_do_test',array(
						'testId'=>$task['targetId']));
					break;
					case  'text':
					$tasksevents[$key]['url'] = $this->generateUrl('classroom_courses',array(
						'classroomId'=>$task['meta']['classroomId']
						));
					break;
					case 'document':
					$tasksevents[$key]['url'] = $this->generateUrl('classroom_courses',array(
						'classroomId'=>$task['meta']['classroomId']
						));
					break;
				}
			}
		}
		$jsontasks = json_encode($tasksevents);
		if($user->isTeacher()){
		return $this->render('TopxiaWebBundle:MyTask:index.html.twig', array(
            'user' => $user,
            'taskjson' => $jsontasks,
            ));
		}
		
	}
	protected function getTaskService()
	{
		return $this->getServiceKernel()->createService('Task.TaskService');
	}
}