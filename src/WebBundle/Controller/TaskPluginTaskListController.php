<?php

namespace WebBundle\Controller;


use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;

class TaskPluginTaskListController extends BaseController
{
    public function pageContentAction($courseSetId, $courseId, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);

        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        $activity = $this->getActivityService()->getActivity($task['id']);
        $tasks    = $this->getTaskService()->findUserTasksFetchActivityAndResultByCourseId($courseId);
        return $this->render('@Web/TaskPlugin/task-list.html.twig', array(
            'tasks'    => $tasks,
            'activity' => $activity
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
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
