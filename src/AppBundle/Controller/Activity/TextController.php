<?php

namespace AppBundle\Controller\Activity;

use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class TextController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);

        if (empty($activity)) {
            throw $this->createNotFoundException('activity not found');
        }

        $text = $this->getActivityService()->getActivityConfig('text')->get($activity['mediaId']);

        if (empty($text)) {
            throw $this->createNotFoundException('text activity not found');
        }

        return $this->render('activity/text/show.html.twig', array(
            'activity' => $activity,
            'text'     => $text
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $text     = $this->getActivityService()->getActivityConfig('text')->get($activity['mediaId']);

        return $this->render('activity/text/modal.html.twig', array(
            'activity' => $activity,
            'text'     => $text
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/text/modal.html.twig');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
