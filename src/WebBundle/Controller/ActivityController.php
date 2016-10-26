<?php
namespace WebBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class ActivityController extends BaseController
{
    public function updateAction(Request $request, $id)
    {
        $activity       = $this->getActivityService()->getActivity($id);
        $config         = $this->getActivityService()->getActivityConfig($activity['mediaType']);
        $editController = $config->getAction('edit');
        return $this->forward($editController, array(
            'id' => $activity['id']
        ));
    }

    public function createAction(Request $request, $type)
    {
        $config           = $this->getActivityService()->getActivityConfig($type);
        $createController = $config->getAction('create');
        return $this->forward($createController);
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
