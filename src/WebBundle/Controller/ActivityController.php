<?php
namespace WebBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class ActivityController extends BaseController
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity         = $this->getActivityService()->getActivity($id);
        $config           = $this->getActivityService()->getActivityConfig($activity['mediaType']);
        $createController = $config->getAction('show');

        return $this->forward($createController, array(
            'courseId' => $courseId,
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

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
