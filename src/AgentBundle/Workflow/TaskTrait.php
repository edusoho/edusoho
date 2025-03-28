<?php

namespace AgentBundle\Workflow;

use AgentBundle\Biz\StudyPlan\Factory\CalculationStrategyFactory;
use DateTime;

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
        $tasks = $this->getTaskService()->searchTasks(['courseId' => $courseId, 'status' => 'published', 'activityIds' => array_column($activities, 'id')], ['number' => 'ASC'], 0, count($activities), ['id', 'activityId', 'title']);
        foreach ($tasks as &$task) {
            $activity = $activities[$task['activityId']];
            $task['duration'] = CalculationStrategyFactory::create($activity)->calculateTime($activity);
            $task['startTime'] = $activity['startTime'];
            $task['endTime'] = $activity['endTime'];
        }

        return $tasks;
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

    private function planTasks($inputs, $tasks)
    {
        $timeLimitTasks = [];
        $noLimitTasks = [];
        foreach ($tasks as $task) {

        }
        $totalSeconds = array_sum(array_column($tasks, 'duration'));
        $startDate = empty($inputs['startDate']) ? date('Y-m-d') : $inputs['startDate'];
        if (!empty($inputs['endDate'])) {
            $studyDateCount = $this->calculateStudyDates($startDate, $inputs['endDate'], $inputs['weekDays']);
            $dailyLearnDuration = max(round(($totalSeconds / $studyDateCount) / 3600, 1), 0.1);
        } else {
            $dailyLearnDuration = $inputs['dailyLearnDuration'];
        }
        $currentDate = new DateTime($startDate);
        $taskSeq = 0;
        $planTasks = [];
        while (true) {
            if (count(array_unique(array_column($planTasks, 'id'))) == count($tasks)) {
                break;
            }
            if (in_array($currentDate->format('N'), $inputs['weekDays'])) {
                $duration = $dailyLearnDuration;
                while ($duration > 0) {
                    $taskDuration = max(round($tasks[$taskSeq]['duration'] / 3600, 1), 0.1);
                    $planTasks[] = [
                        'id' => $tasks[$taskSeq]['id'],
                        'courseId' => $inputs['courseId'],
                        'title' => $tasks[$taskSeq]['title'],
                        'date' => $currentDate->format('Y-m-d'),
                        'duration' => min($taskDuration, $duration),
                    ];
                    if ($duration < $taskDuration) {
                        $tasks[$taskSeq]['duration'] = intval($tasks[$taskSeq]['duration'] - $duration * 3600);
                        $duration = 0;
                    } else {
                        $duration = round($duration - $taskDuration, 1);
                        $taskSeq++;
                    }
                }
            }
            $currentDate->modify('+1 day');
        }

        return $planTasks;
    }

    private function calculateStudyDates($startDate, $endDate, $weekdays)
    {
        $studyDateCount = 0;
        $currentDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);
        while ($currentDate <= $endDate) {
            if (in_array($currentDate->format('N'), $weekdays)) {
                $studyDateCount++;
            }
            $currentDate->modify('+1 day');
        }

        return $studyDateCount;
    }
}
