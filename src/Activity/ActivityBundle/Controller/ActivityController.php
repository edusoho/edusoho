<?php
namespace Activity\ActivityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class ActivityController extends BaseController
{
    public function showAction(Request $request, $id)
    {
        $activity = $this->getActivityService()->getActivity($id);
        return $this->render('ActivityBundle:Activity:show.html.twig', array(
            'activity' => $activity
        ));
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:Activity.ActivityService');
    }
}
