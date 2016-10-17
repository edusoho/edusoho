<?php
namespace CourseTask\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class TaskManageController extends BaseController
{
    public function createAction(Request $request, $courseId)
    {
        if ($request->getMethod() == 'POST') {
            $task      = $request->request->all();
            $savedTask = $this->getTaskService()->createTask($task);

            return $this->render('TaskBundle:TaskManage:list-item.html.twig', array(
                'task' => $savedTask
            ));
        }

        return $this->render('TaskBundle:TaskManage:modal.html.twig', array());
    }

    public function updateAction(Request $request, $courseId, $id)
    {
        if ($request->getMethod() == 'POST') {
            $task      = $request->request->all();
            $savedTask = $this->getTaskService()->updateTask($id, $task);

            return $this->render('TaskBundle:TaskManage:list-item.html.twig', array(
                'task' => $savedTask
            ));
        }

        $task = $this->getTaskService()->getTask($id);
        return $this->render('TaskBundle:TaskManage:modal.html.twig', array(
            'task' => $task
        ));
    }

    public function deleteAction(Request $request, $courseId, $id)
    {
        $this->getTaskService()->deleteTask($id);
        return $this->createJsonResponse(true);
    }

    public function tasksAction(Request $request, $courseId)
    {
        $this->tryManageCourse();
        $tasks = $this->getTaskService()->findTasksByCourseId($courseId);

        return $this->render('TaskBundle:TaskManage:list.html.twig', array(
            'tasks' => $tasks
        ));
    }

    protected function tryManageCourse()
    {
        return true;
    }

    protected function getTaskService()
    {
        return $this->createService('Task:Task.TaskService');
    }
}
