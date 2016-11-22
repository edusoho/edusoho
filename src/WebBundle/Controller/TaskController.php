<?php
namespace WebBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends BaseController
{
    public function showAction(Request $request, $courseId, $id)
    {
        $task     = $this->tryLearnTask($courseId, $id);

        $tasks    = $this->getTaskService()->findTasksWithLearningResultByCourseId($courseId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);

        if(empty($activity)){
            throw $this->createNotFoundException("activity not found");
        }

        $this->getActivityService()->trigger($activity['id'], 'start', array(
            'task' => $task
        ));

        return $this->render('WebBundle:Task:show.html.twig', array(
            'task'     => $task,
            'tasks'    => $tasks,
            'activity' => $activity,
            'types'    => $this->getActivityService()->getActivityTypes()
        ));
    }

    public function taskActivityAction(Request $request, $courseId, $id)
    {
        $task = $this->tryLearnTask($courseId, $id);

        return $this->forward('WebBundle:Activity:show', array(
            'id'       => $task['activityId'],
            'courseId' => $courseId
        ));
    }

    protected function tryLearnTask($courseId, $taskId)
    {
        $this->getCourseService()->tryLearnCourse($courseId);
        $task = $this->getTaskService()->tryTakeTask($taskId);

        if (empty($task)) {
            throw $this->createResourceNotFoundException('task', $taskId);
        }

        if ($task['courseId'] != $courseId) {
            throw $this->createAccessDeniedException();
        }
        return $task;
    }

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
