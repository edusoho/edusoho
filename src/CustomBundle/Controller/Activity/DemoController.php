<?php

namespace CustomBundle\Controller\Activity;

use AppBundle\Controller\Activity\ActivityActionInterface;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class DemoController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity)
    {
        return $this->render('@Custom/activity/demo/show.html.twig');
        // TODO: Implement showAction() method.
    }

    public function editAction(Request $request, $id, $courseId)
    {
        // TODO: Implement editAction() method.
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('@Custom/activity/demo/modal.html.twig', array(
            'courseId' => $courseId,
        ));
    }

    public function previewAction(Request $request, $task)
    {
        // TODO: Implement previewAction() method.
    }

    public function finishConditionAction(Request $request, $activity)
    {
        // TODO: Implement finishConditionAction() method.
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService("Activity:ActivityService");
    }

}