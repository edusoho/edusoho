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

        $updateRealTimeTestResultStatusJob = array(
            'name' => 'updateRealTimeTestResultStatus_activity_'.$activity['id'],
            'nextFireTime' => $testpaperActivity['limitedTime'] * 60 + 3600,
            'class' => str_replace('\\', '\\\\', UpdateRealTimeTestResultStatusJob::class),
            'args' => array(
                'targetType' => 'activity',
                'targetId' => $activity['id'],
            ),
        );

        $this->getScheduler()->schedule($updateRealTimeTestResultStatusJob);
    }

    protected function getScheduler()
    {
        $biz = $this->getBiz();

        return $biz['scheduler'];
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
