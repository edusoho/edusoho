<?php
namespace Activity\ActivityBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;

class ActivityController extends BaseController
{
    public function showAction($task)
    {
        list($activity, $detail, $typeConfg) = $this->getActivityService()->getActivityDetail($task['activityId']);

        return $this->render($typeConfg['show_page'], array(
            'activity' => $activity,
            'detail'   => $detail,
            'task'     => $task
        ));
    }

    public function triggerAction($id, $eventName, $data)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $this->getActivityService()->trigger($id, $eventName, $data);

        return $this->createJsonResponse(true);
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:Activity.ActivityService');
    }
}
