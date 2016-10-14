<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class ActivityManageController extends BaseController
{
    public function createAction(Request $request, $courseId, $type)
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

    public function updateAction(Request $request, $courseId, $id)
    {
    }

    public function deleteAction(Request $request, $courseId, $id)
    {
    }

    public function activitiesAction(Request $request, $courseId)
    {
        $this->tryManageCourse();
        $activities = $this->getActivityService()->findActivitiesByCourseId($courseId);

        return $this->render('ActivityBundle:ActivityManage:list.html.twig', array(
            'activities' => $activities
        ));
    }

    protected function tryManageCourse()
    {
        return true;
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:Activity.ActivityService');
    }
}
