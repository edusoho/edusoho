<?php

namespace Biz\Course\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\LiveActivityService;
use Biz\AppLoggerConstant;
use Biz\Course\Service\LiveReplayService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\LogService;
use Biz\Task\Service\TaskService;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class GenerateReplayJob extends AbstractJob
{
    const DAYTIME = 86400;

    public function execute()
    {
        $activities = $this->getActivityService()->search(
            $this->prepareConditions(),
            [],
            0,
            PHP_INT_MAX
        );
        if (empty($activities)) {
            return;
        }

        $tasks = $this->getTaskService()->findTasksByActivityIds(ArrayToolkit::column($activities, 'id'));
        $activities = ArrayToolkit::index($activities, 'mediaId');
        $liveActivities = $this->getLiveActivityService()->findLiveActivitiesByIds(ArrayToolkit::column($activities, 'mediaId'));
        foreach ($liveActivities as $liveActivity) {
            if ('ungenerated' != $liveActivity['replayStatus'] || empty($liveActivity['roomCreated'])) {
                continue;
            }

            $activity = $activities[$liveActivity['id']];
            try {
                $result = $this->getLiveReplayService()->generateReplay(
                    $liveActivity['liveId'],
                    $activity['fromCourseId'],
                    $activity['id'],
                    $liveActivity['liveProvider'],
                    'live'
                );

                if (array_key_exists('error', $result)) {
                    continue;
                }

                $task = $tasks[$activity['id']];
                $client = new EdusohoLiveClient();
                $result = $client->getMaxOnline($liveActivity['liveId']);

                $this->getTaskService()->setTaskMaxOnlineNum($task['id'], $result['onLineNum']);
            } catch (\Exception $e) {
                $this->getLogService()->error(AppLoggerConstant::COURSE, 'generate_replay', "生成回放失败:{$e->getMessage()}");
            }
        }
    }

    protected function prepareConditions()
    {
        $endTimeGE = strtotime('yesterday');
        $conditions = [
            'endTime_GE' => $endTimeGE,
            'endTime_LT' => $endTimeGE + self::DAYTIME,
            'mediaType' => 'live',
            'copyId' => 0,
        ];

        $multiClasses = $this->getMultiClassService()->findMultiClassesByReplayShow(0);
        if (!empty($multiClasses)) {
            $conditions['excludeCourseIds'] = ArrayToolkit::column($multiClasses, 'courseId');
        }

        return $conditions;
    }

    /**
     * @return LiveReplayService
     */
    protected function getLiveReplayService()
    {
        return $this->biz->service('Course:LiveReplayService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->biz->service('MultiClass:MultiClassService');
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        return $this->biz->service('Activity:LiveActivityService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }
}
