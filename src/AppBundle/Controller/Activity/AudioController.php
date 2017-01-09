<?php

namespace AppBundle\Controller\Activity;


use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class AudioController extends BaseController implements ActivityActionInterface
{

    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id, $fetchMedia = true);

        return $this->render('activity/audio/show.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId
        ));
    }

    public function previewAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId'], $fetchMedia = true);

        return $this->render('activity/audio/preview.html.twig', array(
            'task'     => $task,
            'activity' => $activity,
            'courseId' => $task['courseId']
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id, $fetchMedia = true);
        $activity = $this->fillMinuteAndSecond($activity);
        return $this->render('activity/audio/modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/audio/modal.html.twig', array(
            'courseId' => $courseId
        ));
    }

    public function finishConditionAction($activity)
    {
        return $this->render('activity/audio/finish-condition.html.twig', array());
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
        return $this->createService('Activity:ActivityService');
    }
}