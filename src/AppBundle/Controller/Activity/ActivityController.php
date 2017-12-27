<?php

namespace AppBundle\Controller\Activity;

use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class ActivityController extends BaseController
{
    public function showAction($task, $preview)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        if (empty($activity)) {
            throw $this->createNotFoundException('activity not found');
        }

        $container = $this->get('activity_runtime_container');
        $activity['preview'] = $preview;

        return $container->show($activity);
    }

    public function previewAction($task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        if (empty($activity)) {
            throw $this->createNotFoundException('activity not found');
        }
        $actionConfig = $this->getActivityConfig($activity['mediaType']);

        return $this->forward($actionConfig['controller'].':preview', array(
            'task' => $task,
        ));
    }

    public function updateAction($id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);

        $container = $this->get('activity_runtime_container');

        return $container->update($activity);
    }

    public function createAction($type, $courseId)
    {
        $activity = array(
            'id' => 0,
            'mediaType' => $type,
            'fromCourseId' => $courseId,
        );
        $container = $this->get('activity_runtime_container');

        return $container->create($activity);
    }

    public function customManageRouteAction($fromCourseId, $mediaType, $id, $routeName)
    {
        $activity = array(
            'id' => $id,
            'mediaType' => $mediaType,
            'fromCourseId' => $fromCourseId,
        );

        $container = $this->get('activity_runtime_container');

        return $container->renderRoute($activity, $routeName);
    }

    public function customLearningRouteAction(Request $request, $courseId, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $container = $this->get('activity_runtime_container');
        $routeName = $request->query->get('routeName');
        return $container->renderRoute($activity, $routeName);
    }

    public function triggerAction(Request $request, $courseId, $activityId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $activity = $this->getActivityService()->getActivity($activityId);

        if (empty($activity)) {
            throw $this->createResourceNotFoundException('activity', $activityId);
        }

        $eventName = $request->request->get('eventName');

        if (empty($eventName)) {
            throw $this->createNotFoundException('activity event is empty');
        }

        $data = $request->request->get('data', array());

        $this->getActivityService()->trigger($activityId, $eventName, $data);

        return $this->createJsonResponse(array(
            'event' => $eventName,
            'data' => $data,
        ));
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
     * @return \Biz\Task\Service\TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
