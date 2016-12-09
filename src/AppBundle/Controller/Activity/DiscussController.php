<?php

namespace WebBundle\Controller;

use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class DiscussController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        return $this->render('WebBundle:DiscussActivity:show.html.twig', array(
            'activity' => $activity
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);

        return $this->render('WebBundle:DiscussActivity:modal.html.twig', array(
            'activity' => $activity
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('WebBundle:DiscussActivity:modal.html.twig', array(
            'courseId' => $courseId
        ));
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }
}
