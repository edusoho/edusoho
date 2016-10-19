<?php
namespace CourseTask\TaskBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class TaskManageController extends BaseController
{
    public function createAction(Request $request, $courseId)
    {
        $course = $this->tryManageCourse($courseId);
        if ($request->getMethod() == 'POST') {
            $task      = $request->request->all();
            $savedTask = $this->getTaskService()->createTask($task);

            return $this->render('TaskBundle:TaskManage:list-item.html.twig', array(
                'task' => $savedTask
            ));
        }

        $currentType   = $request->query->get('currentType', '');
        $activityTypes = $this->getActivityService()->getActivityTypes();

        return $this->render('TaskBundle:TaskManage:modal.html.twig', array(
            'activityTypes' => $activityTypes,
            'courseId'      => $courseId,
            'currentType'   => $currentType
        ));
    }

    public function updateAction(Request $request, $courseId, $id)
    {
        $course = $this->tryManageCourse($courseId);
        $task   = $this->getTaskService()->getTask($id);
        if ($task['courseId'] != $courseId) {
            throw $this->createInvalidArgumentException($this->getServiceKernel()->trans('任务不在课程中'));
        }

        if ($request->getMethod() == 'POST') {
            $task      = $request->request->all();
            $savedTask = $this->getTaskService()->updateTask($id, $task);

            return $this->render('TaskBundle:TaskManage:list-item.html.twig', array(
                'task' => $savedTask
            ));
        }

        $activity      = $this->getActivityService()->getActivity($task['activityId']);
        $activityTypes = $this->getActivityService()->getActivityTypes();

        return $this->render('TaskBundle:TaskManage:modal.html.twig', array(
            'task'          => $task,
            'courseId'      => $courseId,
            'activity'      => $activity,
            'currentType'   => $activity['mediaType'],
            'activityTypes' => $activityTypes
        ));
    }

    public function deleteAction(Request $request, $courseId, $id)
    {

        $course = $this->tryManageCourse($courseId);
        $task   = $this->getTaskService()->getTask($id);
        if ($task['courseId'] != $courseId) {
            throw $this->createInvalidArgumentException($this->getServiceKernel()->trans('任务不在课程中'));
        }

        $this->getTaskService()->deleteTask($id);
        return $this->createJsonResponse(true);
    }

    // TODO 是否移到CourseManageController
    public function tasksAction(Request $request, $courseId)
    {   
        $courseItems = $this->getCourseService()->getCourseItems($courseId);
        $course = $this->tryManageCourse($courseId);
        $tasks  = $this->getTaskService()->findTasksByCourseId($courseId);
        return $this->render('TaskBundle:TaskManage:list.html.twig', array(
            'tasks' => $tasks,
            'course'=> $course,
            'items'=>$courseItems,
        ));
    }

    protected function tryManageCourse($courseId)
    {
        return $this->getCourseService()->tryManageCourse($courseId);
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getTaskService()
    {
        return $this->createService('CourseTask:Task.TaskService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:Activity.ActivityService');
    }
}
