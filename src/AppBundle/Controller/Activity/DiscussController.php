<?php

namespace AppBundle\Controller\Activity;

use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class DiscussController extends BaseActivityController implements ActivityActionInterface
{
    public function previewAction(Request $request, $task)
    {
        return $this->render('activity/no-preview.html.twig');
    }

    public function showAction(Request $request, $activity)
    {
        return $this->render('activity/discuss/show.html.twig', array(
            'activity' => $activity,
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);

        return $this->render('activity/discuss/modal.html.twig', array(
            'activity' => $activity,
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/discuss/modal.html.twig', array(
            'courseId' => $courseId,
        ));
    }

    public function finishConditionAction(Request $request, $activity)
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
