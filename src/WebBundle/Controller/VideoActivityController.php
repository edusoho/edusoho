<?php
/**
 * User: Edusoho V8
 * Date: 26/10/2016
 * Time: 19:25
 */

namespace WebBundle\Controller;


use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class VideoActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);

        return $this->render('WebBundle:VideoActivity:show.html.twig',array(
            'activity'=>$activity
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $activity = $this->fillMinuteAndSecond($activity);
        return $this->render('WebBundle:VideoActivity:modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('WebBundle:VideoActivity:modal.html.twig', array(
            'courseId' => $courseId
        ));
    }


    protected function fillMinuteAndSecond($activity)
    {
        if (!empty($activity['length'])) {
            $activity['minute'] = intval($activity['length'] / 60);
            $activity['second'] = intval($activity['length'] % 60);
        }
        return $activity;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }


}