<?php

namespace AppBundle\Controller\Activity;

use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class DiscussController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        return $this->render('activity/discuss/show.html.twig', array(
            'activity' => $activity
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);

        return $this->render('activity/discuss/modal.html.twig', array(
            'activity' => $activity
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/discuss/modal.html.twig', array(
            'courseId' => $courseId
        ));
    }

    public function finishConditionAction($activity)
    {
        return $this->render('activity/discuss/finish-condition.html.twig', array());
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
