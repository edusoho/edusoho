<?php
namespace WebBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class ActivityController extends BaseController
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);

        if (empty($activity)) {
            throw $this->createNotFoundException('activity not found');
        }

        $config         = $this->getActivityService()->getActivityConfig($activity['mediaType']);
        $showController = $config->getAction('show');
        return $this->forward($showController, array(
            'id'      => $id,
            'courseId' => $courseId,
        ));
    }

    public function updateAction(Request $request, $id, $courseId)
    {
        $activity       = $this->getActivityService()->getActivity($id);
        $config         = $this->getActivityService()->getActivityConfig($activity['mediaType']);
        $editController = $config->getAction('edit');
        return $this->forward($editController, array(
            'id'       => $activity['id'],
            'courseId' => $courseId
        ));
    }

    public function createAction(Request $request, $type, $courseId)
    {
        $config           = $this->getActivityService()->getActivityConfig($type);
        $createController = $config->getAction('create');
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

    public function playerAction(Request $request, $id, $courseId)
    {
        $activity         = $this->getActivityService()->getActivity($id);
        $config           = $this->getActivityService()->getActivityConfig($activity['mediaType']);
        $createController = $config->getAction('player');

        return $this->forward($createController, array(
            'id'       => $activity['id'],
            'courseId' => $courseId
        ));
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }


    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

}
