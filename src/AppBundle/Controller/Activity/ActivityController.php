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
        $actionConfig = $this->getActivityConfig($activity['mediaType']);

        return $this->forward($actionConfig['controller'].':edit', array(
            'id' => $activity['id'],
            'courseId' => $courseId,
        ));
    }

    public function createAction($type, $courseId)
    {
        $actionConfig = $this->getActivityConfig($type);

        return $this->forward($actionConfig['controller'].':create', array(
            'courseId' => $courseId,
        ));
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
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
