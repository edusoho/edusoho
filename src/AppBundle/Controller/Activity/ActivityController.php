<?php
namespace AppBundle\Controller\Activity;

use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\VarDumper\Cloner\VarCloner;

class ActivityController extends BaseController
{

    public function showAction(Request $request, $id, $courseId, $preview)
    {
        $activity = $this->getActivityService()->getActivity($id);

        if (empty($activity)) {
            throw $this->createNotFoundException('activity not found');
        }
        $actionConfig = $this->getActivityActionConfig($activity['mediaType']);
        return $this->forward($actionConfig['show'], array(
            'id'       => $id,
            'courseId' => $courseId,
            'preview'  => $preview
        ));
    }

    public function previewAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId']);

        if (empty($activity)) {
            throw $this->createNotFoundException('activity not found');
        }
        $actionConfig = $this->getActivityActionConfig($activity['mediaType']);
        return $this->forward($actionConfig['preview'], array(
            'task' => $task
        ));
    }

    public function updateAction($id, $courseId)
    {
        $activity     = $this->getActivityService()->getActivity($id);
        $actionConfig = $this->getActivityActionConfig($activity['mediaType']);
        return $this->forward($actionConfig['edit'], array(
            'id'       => $activity['id'],
            'courseId' => $courseId
        ));
    }

    public function createAction($type, $courseId)
    {
        $actionConfig     = $this->getActivityActionConfig($type);
        $createController = $actionConfig['create'];
        return $this->forward($createController, array(
            'courseId' => $courseId
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
            'data'  => $data
        ));
    }

    protected function getActivityConfig()
    {
        return $this->get('extension.default')->getActivities();
    }

    protected function getActivityActionConfig($type)
    {
        $config = $this->getActivityConfig();
        return $config[$type]['actions'];
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
