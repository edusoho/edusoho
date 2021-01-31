<?php

namespace TrainingTaskPlugin\Controller\Activity;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class TrainingController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity)
    {
        return $this->render('TrainingTaskPlugin:Activity/Training/show.html.twig', array(
            'activity' => $activity,
            // other params
        ));
    }

    public function previewAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId']);

        // code

        return $this->render('TrainingTaskPlugin:Activity/Training/preview.html.twig', array(
            'activity' => $activity,
            // other params
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);

        // code

        return $this->render('TrainingTaskPlugin:Activity/Training/modal.html.twig', array(
            'activity' => $activity,
            // other params
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        //code

        return $this->render('TrainingTaskPlugin:Activity/Training/modal.html.twig', array(
            'courseId' => $courseId,
            // other params
        ));
    }

    public function finishConditionAction(Request $request, $activity)
    {
        $media = $this->getActivityService()->getActivityConfig('training')->get($activity['mediaId']);

        return $this->render('TrainingTaskPlugin:Activity/Training/finish-condition.html.twig', array(
            'media' => $media,
        ));
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    public function getXXXService()
    {
        return $this->createService('TrainingTaskPlugin:XXX:XXXService');
    }
}