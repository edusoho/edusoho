<?php

namespace WebBundle\Controller;



use Biz\Task\Service\TaskService;

class TaskPluginTaskListController extends BaseController
{
    public function pageContentAction($courseSetId, $courseId, $taskId)
    {
        $tasks    = $this->getTaskService()->findUserTasksFetchActivityAndResultByCourseId($courseId);

        return $this->render('@Web/TaskPlugin/task-list.html.twig', array('tasks' => $tasks));
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
