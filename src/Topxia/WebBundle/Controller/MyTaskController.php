<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class MyTaskController extends BaseController
{
	public function indexAction(Request $request)
	{
		$user = $this->getCurrentUser();
		$conditions=array('userId' => $user['id']);

		$tasks = $this->getTaskService()->searchTasks($conditions,array('taskStartTime','ASC'),0,9999);
		$tasksevents = array();

		if($tasks){
			foreach($tasks as $key => $task){
				$tasksevents[$key]['title'] = $task['title'];
				$tasksevents[$key]['start'] = date("Y-m-d H:i:s",$task['taskStartTime']);
				$tasksevents[$key]['end'] = date("Y-m-d H:i:s",strtotime('+1 day', $task['taskEndTime']));
				$tasksevents[$key]['id'] = $task['id'];
				if($task['taskType']=='studyplan'){
					if ($task['targetType'] == 'homework') {
						$tasksevents[$key]['url'] = $this->generateUrl('course_homework_start_do', array(
							'courseId'=>$task['meta']['courseId'],'homeworkId'=>$task['targetId']));
					} else {
						$tasksevents[$key]['url'] = $this->generateUrl('course_learn',array(
						'id' => $task['meta']['courseId'])).'#lesson/'.$task['targetId'];
					}
				}

				if($task['status']=='completed'){
					$tasksevents[$key]['color']='#46c37b';
				}else{
					$tasksevents[$key]['color']='#919191';
				}
			}/*else{//其他扩展任务url
				}*/
		}else{
			$tasksevents = array(array(
				'title'=>'并没有任务',
				'start' => date("Y-m-d",time()),
				'end' => date("Y-m-d",time()),
			));
		}

		$jsontasks = json_encode($tasksevents);

		return $this->render('TopxiaWebBundle:MyTask:index.html.twig', array(
	        'user' => $user,
	        'taskjson' => $jsontasks,
	        'today' =>date("Y-m-d",time()),
        ));
	}


	protected function getTaskService()
	{
		return $this->getServiceKernel()->createService('Task.TaskService');
	}
}