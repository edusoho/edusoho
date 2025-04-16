<?php

namespace AgentBundle\Workflow;

use AgentBundle\Biz\StudyPlan\Factory\CalculationStrategyFactory;

trait TaskTrait
{
    private function findSchedulableTasks($courseId)
    {
        $taskResults = $this->getTaskResultService()->findUserFinishedTaskResultsByCourseId($courseId);
        $conditions = ['fromCourseId' => $courseId, 'status' => 'published', 'mediaTypes' => ['text', 'video', 'audio', 'live', 'doc', 'ppt', 'testpaper', 'replay']];
        if (!empty($taskResults)) {
            $conditions['excludeIds'] = array_column($taskResults, 'activityId');
        }
        $activities = $this->getActivityService()->search(
            $conditions,
            [],
            0,
            PHP_INT_MAX
        );
        $activities = $this->filterSchedulableActivities($activities);
        if (empty($activities)) {
            return [];
        }
        $activities = $this->getActivityService()->findActivities(array_column($activities, 'id'));
        $activities = array_column($activities, null, 'id');
        $tasks = $this->getTaskService()->searchTasks(['courseId' => $courseId, 'status' => 'published', 'activityIds' => array_column($activities, 'id')], ['seq' => 'ASC'], 0, count($activities), ['id', 'activityId', 'title', 'type']);
        foreach ($tasks as &$task) {
            $activity = $activities[$task['activityId']];
            $task['duration'] = $this->calculateDuration($activity);
            $task['startTime'] = $activity['startTime'];
            $task['endTime'] = $activity['endTime'];
        }

        return $tasks;
    }

    private function calculateDuration($activity)
    {
        return CalculationStrategyFactory::create($activity)->calculateTime($activity);
    }

    private function filterSchedulableActivities($activities)
    {
        return array_filter($activities, function ($activity) {
            if (in_array($activity['mediaType'], ['text', 'video', 'audio', 'doc', 'replay'])) {
                return true;
            }
            if ('ppt' == $activity['mediaType']) {
                return 'time' == $activity['finishType'];
            }
            if ('live' == $activity['mediaType']) {
                return ('time' == $activity['finishType']) && ($activity['startTime'] > time());
            }
            if ('testpaper' == $activity['mediaType']) {
                return ($activity['length'] > 0) && (empty($activity['endTime']) || ($activity['endTime'] > time()));
            }

            return false;
        });
    }

    private function scheduleTasks($inputs, $tasks)
    {
        $taskScheduler = new TaskScheduler();

        return $taskScheduler->schedule($inputs, $tasks);
    }
}
