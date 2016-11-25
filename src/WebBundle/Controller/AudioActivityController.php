<?php
/**
 * User: Edusoho V8
 * Date: 02/11/2016
 * Time: 14:03
 */

namespace WebBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

class AudioActivityController extends BaseController implements ActivityActionInterface
{

    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        return $this->render('WebBundle:AudioActivity:show.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $activity = $this->fillMinuteAndSecond($activity);
        return $this->render('WebBundle:AudioActivity:modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('WebBundle:AudioActivity:modal.html.twig', array(
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

    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }
}