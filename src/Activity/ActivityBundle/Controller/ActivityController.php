<?php
namespace Activity\ActivityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class ActivityController extends BaseController
{
    public function showAction(Request $request, $id)
    {
        list($activity, $detail, $typeConfg) = $this->getActivityService()->getDetailActivity($id);

        return $this->render($typeConfg['show_page'], array(
            'activity' => $activity,
            'detail'   => $detail
        ));
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:Activity.ActivityService');
    }
}
