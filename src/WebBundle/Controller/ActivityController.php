<?php
namespace WebBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class ActivityController extends BaseController
{
    public function showAction(Request $request, $id, $taskId, $courseId)
    {
        $activity       = $this->getActivityService()->getActivity($id);
        $config         = $this->getActivityService()->getActivityConfig($activity['mediaType']);
        $showController = $config->getAction('show');

        return $this->forward($showController, array(
            'courseId' => $courseId,
            'taskId'   => $taskId,
            'id'       => $id
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

    public function triggerAction($id, $eventName, $data)
    {
        $this->getActivityService()->trigger($id, $eventName, $data);
        return $this->createJsonResponse(true);
    }

    public function playerAction(Request $request, $courseId, $activityId)
    {
        $this->getCourseService()->tryLearnCourse($courseId);
        $activity = $this->getActivityService()->getActivity($activityId);
        if (empty($activity)) {
            $this->createResourceNotFoundException('activity', $activityId);
        }
        $context = array();
        return $this->forward('TopxiaWebBundle:Player:show', array(
            'id'      => $activity['ext']["mediaId"],
            'context' => $context
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
