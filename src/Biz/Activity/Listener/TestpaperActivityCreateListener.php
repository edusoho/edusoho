<?php

namespace Biz\Activity\Listener;

use Biz\Task\Service\TaskService;

class TestpaperActivityCreateListener extends Listener
{
    /*
     * testpaper realtime mode job
     */

    public function handle($activity, $data)
    {
        if ($activity['mediaType'] != 'testpaper') {
            return;
        }

        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        if (empty($testpaperActivity['limitedTime']) || $testpaperActivity['testMode'] != 'realTime' || empty($activity['startTime'])) {
            return;
        }

        $second = $testpaperActivity['limitedTime'] * 60 + 3600;

        $updateRealTimeTestResultStatusJob = array(
            'name' => 'updateRealTimeTestResultStatus',
            'cycle' => 'once',
            'jobClass' => 'Biz\\Testpaper\\Job\\UpdateRealTimeTestResultStatusJob',
            'jobParams' => '',
            'targetType' => 'activity',
            'targetId' => $activity['id'],
            'nextExcutedTime' => $activity['startTime'] + $second,
        );

        $this->getCrontabJobService()->createJob($updateRealTimeTestResultStatusJob);
    }

    /**
     * @return TaskService
     */
    protected function getTestpaperActivityService()
    {
        return $this->getBiz()->service('Activity:TestpaperActivityService');
    }

    protected function getCrontabJobService()
    {
        return $this->getBiz()->service('Crontab:CrontabService');
    }
}
