<?php

namespace AppBundle\Controller\Activity;

use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class AudioController extends BaseActivityController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity)
    {
        $audio = $this->getActivityService()->getActivityConfig($activity['mediaType'])->get($activity['mediaId']);

        return $this->render('activity/audio/show.html.twig', array(
            'activity' => $activity,
            'audio' => $audio,
        ));
    }

    public function previewAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId'], $fetchMedia = true);

        return $this->render('activity/audio/preview.html.twig', array(
            'task' => $task,
            'activity' => $activity,
            'courseId' => $task['courseId'],
            'disableModeSelection' => true, //音频任务非学员预览屏蔽顺序播放
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id, $fetchMedia = true);
        $activity = $this->fillMinuteAndSecond($activity);

        return $this->render('activity/audio/modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId,
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/audio/modal.html.twig', array(
            'courseId' => $courseId,
        ));
    }

    public function finishConditionAction(Request $request, $activity)
    {
        return $this->render('activity/audio/finish-condition.html.twig', array());
    }

    protected function fillMinuteAndSecond($activity)
    {
        if (!empty($activity['length'])) {
            $activity['minute'] = (int) ($activity['length'] / 60);
            $activity['second'] = (int) ($activity['length'] % 60);
        }

        return $activity;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
