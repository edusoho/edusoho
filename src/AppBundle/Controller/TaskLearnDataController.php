<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class TaskLearnDataController extends BaseController
{
    public function learnDataDetailAction(Request $request, $courseId, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        if (empty($task)) {
            return $this->createMessageResponse('error', 'task not found');
        }
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $config = $this->getActivityConfig($task['type']);

        return $this->forward($config['controller'].':learnDataDetail', array(
            'task' => $task,
        ));
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getActivityConfig($type)
    {
        $config = $this->get('extension.manager')->getActivities();

        return $config[$type];
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \AppBundle\Twig\WebExtension
     */
    protected function getWebExtension()
    {
        return $this->container->get('web.twig.extension');
    }
}
