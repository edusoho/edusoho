<?php
namespace CourseTask\TaskBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class TaskController extends BaseController
{
    public function showAction(Request $request, $courseId, $id)
    {
        $this->getCourseService()->tryLearnCourse($courseId);
        $task = $this->getTaskService()->getTask($id);

        return $this->render('TaskBundle:Task:show.html.twig', array(
            'task' => $task
        ));
    }

    public function taskActivityAction(Request $request, $courseId, $id)
    {
        $this->getCourseService()->tryLearnCourse($courseId);
        $task = $this->getTaskService()->getTask($id);
        return $this->forward('ActivityBundle:Activity:show', array(
            'request' => $request,
            'id'      => $task['activityId']
        ));
    }

    public function triggerAction(Request $request, $courseId, $id, $eventName)
    {
        $this->getCourseService()->tryLearnCourse($courseId);
        $task = $this->getTaskService()->getTask($id);
        return $this->forward('ActivityBundle:Activity:trigger', array(
            'request'   => $request,
            'id'        => $task['activityId'],
            'eventName' => $eventName
        ));
    }

    public function finishAction(Request $request, $courseId, $id)
    {
        $this->getCourseService()->tryLearnCourse($courseId);
        $this->getTaskService()->finishTask($id);
        return $this->createJsonResponse(true);
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getTaskService()
    {
        return $this->createService('CourseTask:Task.TaskService');
    }
}
