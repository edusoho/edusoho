<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class TaskManageController extends BaseController
{
    public function createAction(Request $request, $courseId, $type)
    {
        if ($request->getMethod() == 'POST') {
            $activity      = $request->request->all();
            $savedActivity = $this->getActivityService()->createActivity($activity);

            return $this->render('TaskBundle:TaskManage:list-item.html.twig', array(
                'activity' => $savedActivity
            ));
        }

        return $this->render('TaskBundle:TaskManage:modal.html.twig', array());
    }

    public function updateAction(Request $request, $courseId, $id)
    {
        if ($request->getMethod() == 'POST') {
            $activity      = $request->request->all();
            $savedActivity = $this->getActivityService()->updateActivity($id, $activity);

            return $this->render('TaskBundle:TaskManage:list-item.html.twig', array(
                'activity' => $savedActivity
            ));
        }

        $activity = $this->getActivityService()->getActivity($id);
        return $this->render('TaskBundle:TaskManage:modal.html.twig', array(
            'activity' => $activity
        ));
    }

    public function deleteAction(Request $request, $courseId, $id)
    {
        $this->getActivityService()->deleteActivity($id);
        return $this->createJsonResponse(true);
    }

    public function activitiesAction(Request $request, $courseId)
    {
        $this->tryManageCourse();
        $activities = $this->getActivityService()->findActivitiesByCourseId($courseId);

        return $this->render('TaskBundle:TaskManage:list.html.twig', array(
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
