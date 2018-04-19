<?php

namespace Biz\Live\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use AppBundle\Common\ArrayToolkit;
use Biz\Util\EdusohoLiveClient;
use Biz\Activity\Service\LiveActivityService;

class UpdateLiveStatusJob extends AbstractJob
{
    private $liveApi;

    public function execute()
    {
        $courseLives = $this->findLivesByActivity();
        $openCourseLives = $this->findLivesByOpenCourseLesson();

        $lives = array_merge($courseLives, $openCourseLives);

        $results = $this->checkLiveStatusFromCloud($lives);
        $this->updateLivesStatus($lives, $results);
    }

    private function findLivesByActivity()
    {
        $activities = $this->getActivityService()->findFinishedLivesWithinTwoHours();
        if (empty($activities)) {
            return array();
        }

        $mediaIds = ArrayToolkit::column($activities, 'mediaId');

        $conditions = array(
            'ids' => $mediaIds,
            'replayStatus' => 'ungenerated',
            'progressStatusNotEqual' => EdusohoLiveClient::LIVE_STATUS_CLOSED,
        );
        $liveActivities = $this->getLiveActivityService()->search($conditions, null, 0, PHP_INT_MAX);

        if (empty($liveActivities)) {
            return array();
        }

        $lives = array();
        foreach ($liveActivities as $live) {
            $lives[] = array('id' => $live['id'], 'type' => 'course', 'liveId' => $live['liveId'], 'liveProvider' => $live['liveProvider']);
        }

        return $lives;
    }

    private function findLivesByOpenCourseLesson()
    {
        $lessons = $this->getOpenCourseService()->findFinishedLivesWithinTwoHours();

        if (empty($lessons)) {
            return array();
        }

        $lives = array();
        foreach ($lessons as $lesson) {
            $lives[] = array('id' => $lesson['id'], 'type' => 'openCourse', 'liveId' => $lesson['mediaId'], 'liveProvider' => $lesson['liveProvider']);
        }

        return $lives;
    }

    private function checkLiveStatusFromCloud($lives)
    {
        $formatLives = $this->formatLives($lives);
        if (empty($formatLives)) {
            return array();
        }

        $client = $this->createLiveApi();
        $results = $client->checkLiveStatus($formatLives);

        return $results;
    }

    private function formatLives($lives)
    {
        if (empty($lives)) {
            return array();
        }

        $format = array();
        foreach ($lives as $live) {
            $format[$live['liveProvider']][] = $live['liveId'];
        }

        return $format;
    }

    private function updateLivesStatus($lives, $livesStatus)
    {
        if (empty($livesStatus)) {
            return;
        }

        foreach ($lives as $live) {
            if (!isset($livesStatus[$live['liveId']])) {
                continue;
            }

            $status = $livesStatus[$live['liveId']];
            if ($status == EdusohoLiveClient::LIVE_STATUS_UNSTART) {
                continue;
            }

            if ($live['type'] == 'openCourse') {
                $this->updateOpenCourseLiveLessonStatus($live, $status);
            } else {
                $this->updateCourseLiveStatus($live, $status);
            }
        }
    }

    private function createLiveApi()
    {
        if (!$this->liveApi) {
            $this->liveApi = new EdusohoLiveClient();
        }

        return $this->liveApi;
    }

    private function updateCourseLiveStatus($live, $status)
    {
        $this->getLiveActivityService()->updateLiveStatus($live['id'], $status);
    }

    private function updateOpenCourseLiveLessonStatus($live, $status)
    {
        $this->getOpenCourseService()->updateLiveStatus($live['id'], $status);
    }

    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    protected function getLiveActivityService()
    {
        return $this->biz->service('Activity:LiveActivityService');
    }

    protected function getOpenCourseService()
    {
        return $this->biz->service('OpenCourse:OpenCourseService');
    }
}
