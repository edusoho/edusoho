<?php

namespace WebBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class LiveActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        return $this->render('WebBundle:LiveActivity:show.html.twig', array(
            'activity' => $activity
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);

        return $this->render('WebBundle:LiveActivity:modal.html.twig', array(
            'activity' => $this->formatTimeFields($activity)
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('WebBundle:LiveActivity:modal.html.twig', array(
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

    //int to datetime
    protected function formatTimeFields($fields)
    {
        $format = 'Y-m-d'; //Y-m-d H:i
        if (isset($fields['startTime'])) {
            $fields['startTime'] = date($format, $fields['startTime']);
        }
        if (isset($fields['endTime'])) {
            $fields['endTime'] = date($format, $fields['endTime']);
        }

        return $fields;
    }
}
