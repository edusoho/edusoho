<?php

namespace WebBundle\Controller;


use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;

class TaskPluginTaskListController extends BaseController
{
    public function pageContentAction(Request $request, $courseId, $taskId)
    {
        list($course) = $this->getCourseService()->tryTakeCourse($courseId);

        $task = $this->getTaskService()->getTask($taskId);

        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        $preview = $request->query->get('preview', false);

        $activity = $this->getActivityService()->getActivity($task['id']);
        $tasks    = $this->getTaskService()->findUserTasksFetchActivityAndResultByCourseId($courseId);
        return $this->render('@Web/TaskPlugin/task-list.html.twig', array(
            'tasks'    => $tasks,
            'course'   => $course,
            'activity' => $activity,
            'preview'  => $preview
        ));
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
