<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class ActivityManageController extends BaseController
{
    public function createAction(Request $request, $type)
    {
        if ($request->getMethod() == 'POST') {
            $activity      = $request->request->all();
            $savedActivity = $this->getActivityService()->createActivity($activity);

            return $this->render('ActivityBundle:ActivityManage:list-item.html.twig', array(
                'activity' => $savedActivity
            ));
        }

        return $this->render('ActivityBundle:ActivityManage:modal.html.twig', array());
    }

    public function updateAction(Request $request, $id)
    {
    }

    public function deleteAction(Request $request, $id)
    {
    }

    public function activitiesAction(Request $request, $planId)
    {
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:Activity.ActivityService');
    }
}
