<?php
namespace WebBundle\Controller;


use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\InvalidArgumentException;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Course\CourseService;

class TaskManageController extends BaseController
{
    public function createAction(Request $request, $courseId)
    {
        $course = $this->tryManageCourse($courseId);

        return $this->render('WebBundle:TaskManage:modal.html.twig', array(
            'course' => $course,
            'types'  => $this->getActivityService()->getActivityTypes()
        ));
    }

    public function taskFieldsAction(Request $request, $courseId, $type)
    {
        $course = $this->tryManageCourse($courseId);
        if ($request->getMethod() == 'POST') {
            $task              = $request->request->all();
            $task['mediaType'] = $type;
            $savedTask         = $this->getTaskService()->createTask($task);
            return $this->createJsonResponse(true);
        }

        $activity         = $this->getActivityService()->getActivityConfig($type);
        $createController = $activity->getAction('create');

        return $this->render('WebBundle:TaskManage:task-fields.html.twig', Array(
            'activity_controller'  => $createController,
            'course'      => $course,
            'currentType' => $type,
            'types'       => $this->getActivityService()->getActivityTypes()
        ));
    }

    public function updateAction(Request $request, $courseId, $id)
    {
        $course = $this->tryManageCourse($courseId);
        $task   = $this->getTaskService()->getTask($id);
        if ($task['courseId'] != $courseId) {
            throw new InvalidArgumentException('任务不在课程中');
        }

        if ($request->getMethod() == 'POST') {
            $task      = $request->request->all();
            $savedTask = $this->getTaskService()->updateTask($id, $task);

            return $this->render('WebBundle:TaskManage:list-item.html.twig', array(
                'task' => $savedTask
            ));
        }

        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $config = $this->getActivityService()->getActivityConfig($activity['mediaType']);
        $editController = $config->getAction('edit');

        return $this->render('WebBundle:TaskManage:modal.html.twig', array(
            'task'        => $task,
            'course'    => $course,
            'activity'    => $activity,
            'currentType' => $activity['mediaType'],
            'activity_controller'    => $editController
        ));
    }

    public function deleteAction(Request $request, $courseId, $id)
    {

        $course = $this->tryManageCourse($courseId);
        $task   = $this->getTaskService()->getTask($id);
        if ($task['courseId'] != $courseId) {
            throw new InvalidArgumentException('任务不在课程中');
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
