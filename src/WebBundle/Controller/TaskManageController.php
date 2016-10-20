<?php
namespace WebBundle\Controller;


use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Course\CourseService;

class TaskManageController extends BaseController
{
    public function activityTypesAction(Request $request, $courseId)
    {
        $course = $this->tryManageCourse($courseId);
        
        return $this->render('WebBundle:TaskManage:edit-modal.html.twig', array(
            'course' => $course,
            'types'  => $this->getActivityService()->getActivityTypes()
        ));        
    }
    public function createAction(Request $request, $courseId)
    {
        $course = $this->tryManageCourse($courseId);
        if ($request->getMethod() == 'POST') {
            $task      = $request->request->all();
            $savedTask = $this->getTaskService()->createTask($task);

            return $this->render('WebBundle:TaskManage:list-item.html.twig', array(
                'task' => $savedTask
            ));
        }

        $currentType = $request->query->get('currentType', '');

        if(empty($currentType)){
            $renderer = null;
        }else{
            $activity = $this->getActivityService()->getActivityModel($currentType);
            $renderer = $activity->getRenderer();
        }

        return $this->render('WebBundle:TaskManage:edit-activity.html.twig', Array(
            'renderer'    => $renderer,
            'course'      => $course,
            'currentType' => $currentType,
            'types'       => $this->getActivityService()->getActivityTypes()
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

            return $this->render('WebBundle:TaskManage:list-item.html.twig', array(
                'task' => $savedTask
            ));
        }

        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $renderer = $this->getActivityService()->getActivityModel($activity['mediaType']);

        return $this->render('WebBundle:TaskManage:edit-modal.html.twig', array(
            'task'        => $task,
            'courseId'    => $courseId,
            'activity'    => $activity,
            'currentType' => $activity['mediaType'],
            '$renderer'   => $renderer
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
        $course      = $this->tryManageCourse($courseId);
        $tasks       = $this->getTaskService()->findTasksByCourseId($courseId);
        return $this->render('WebBundle:TaskManage:list.html.twig', array(
            'tasks'  => $tasks,
            'course' => $course,
            'items'  => $courseItems,
        ));
    }

    protected function tryManageCourse($courseId)
    {
        return $this->getCourseService()->tryManageCourse($courseId);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
