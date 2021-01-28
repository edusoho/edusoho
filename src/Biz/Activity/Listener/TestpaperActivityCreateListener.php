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
            'expression' => $testpaperActivity['limitedTime'] * 60 + 3600,
            'class' => 'Biz\Testpaper\Job\UpdateRealTimeTestResultStatusJob',
            'args' => array(
                'targetType' => 'activity',
                'targetId' => $activity['id'],
            ),
        );

        $this->getSchedulerService()->register($updateRealTimeTestResultStatusJob);
    }

    protected function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return TaskService
     */
    protected function getTestpaperActivityService()
    {
        return $this->getBiz()->service('Activity:TestpaperActivityService');
    }
}
