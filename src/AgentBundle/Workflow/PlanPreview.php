<?php

namespace AgentBundle\Workflow;

use DateTime;

class PlanPreview extends AbstractWorkflow
{
    use TaskTrait;

    public function execute($inputs)
    {
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($inputs['courseId']);
        if (empty($agentConfig['isActive'])) {
            return [
                'ok' => false,
                'error' => [
                    'code' => 'AI_DISABLED',
                    'message' => '伴学服务未开启',
                ],
            ];
        }
        if (empty($inputs['endDate']) && empty($inputs['dailyLearnDuration'])) {
            return [
                'ok' => false,
                'error' => [
                    'code' => 'PARAMS_ERROR',
                    'message' => 'endDate和dailyLearnDuration不能都为空',
                ],
            ];
        }
        $tasks = $this->findSchedulableTasks($inputs['courseId']);
        if (empty($tasks)) {
            return [
                'ok' => false,
                'error' => [
                    'code' => 'NO_LESSON_CAN_PLAN',
                    'message' => '无法制定学习计划',
                ],
            ];
        }

        return [
            'ok' => true,
            'outputs' => [
                'tasks' => $this->planTasks($inputs, $tasks),
            ],
        ];
    }

    private function planTasks($inputs, $tasks)
    {
        $timeLimitTasks = [];
        $noLimitTasks = [];
        foreach ($tasks as $task) {

        }
        $totalSeconds = array_sum(array_column($tasks, 'duration'));
        $startDate = $inputs['startDate'] ?? date('Y-m-d');
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
