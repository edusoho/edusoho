<?php
namespace WebBundle\Controller;


use Biz\Activity\Service\ActivityService;

class ActivityController extends BaseController
{
    public function showAction($task)
    {

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
        return $this->createService('Activity:Activity.ActivityService');
    }
}
