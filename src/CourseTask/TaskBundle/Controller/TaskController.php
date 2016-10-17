<?php
namespace CourseTask\TaskBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class TaskController extends BaseController
{
    public function showAction(Request $request, $id)
    {
        $task = $this->getTaskService()->getTask($id);
        return $this->render('TaskBundle:Task:show.html.twig', array(
            'task' => $task
        ));
    }

    public function taskActivityAction(Request $request, $id)
    {
        $task = $this->getTaskService()->getTask($id);
        return $this->forward('ActivityBundle:Activity:show', array(
            'request' => $request,
            'id'      => $task['activityId']
        ));
    }

    protected function getTaskService()
    {
        return $this->createService('CourseTask:Task.TaskService');
    }
}
