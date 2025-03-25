<?php

namespace AgentBundle\Workflow;

use AgentBundle\Biz\StudyPlan\Factory\CalculationStrategyFactory;
use DateTime;

class PlanGenerate extends AbstractWorkflow
{
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
        $plan = $this->getStudyPlanService()->generatePlan($inputs);

        return [
            'ok' => true,
            'outputs' => [
                'content' => $this->makeMarkdown($plan),
            ],
        ];
    }

    private function makeMarkdown($plan)
    {
        $course = $this->getCourseService()->getCourse($plan['courseId']);
        $studyDates = $this->calculateStudyDates($plan['startDate'], $plan['endDate'], $plan['weekDays']);
        $studyDateCount = count($studyDates);
        $tasks = $this->findToLearnTasks($plan['courseId']);
        $taskCount = count($tasks);
        $learnHour = intval(array_sum(array_column($tasks, 'learnTime')) / 3600);
        $everytimeLearnHour = max(round($learnHour / $studyDateCount, 1), 0.1);

        return <<<MARKDOWN
根据上述内容为你生成以下学习计划：

1、学习内容：{$course['courseSetTitle']}，共{$taskCount}个任务，学完需要{$learnHour}小时

2、学习时间：{$this->makeStudyBoundaryDate($plan['startDate'])} 至 {$this->makeStudyBoundaryDate($plan['endDate'])} 内，每{$this->makeChineseWeekDays($plan['weekDays'])}，共计{$studyDateCount}个学习日

3、每次至少学习：{$everytimeLearnHour}小时

我会在每个学习日提醒你完成学习，期待你的参与！

{$this->makeTable($plan['courseId'], $studyDates, $tasks)}
点击「学习内容」链接直达
MARKDOWN;
    }

    private function calculateStudyDates($startDate, $endDate, $weekdays)
    {
        $studyDates = [];
        $currentDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);
        while ($currentDate <= $endDate) {
            if (in_array($currentDate->format('N'), $weekdays)) {
                $studyDates[] = [
                    'date' => $currentDate->format('m月d日'),
                    'weekday' => $this->convertChineseWeekDay($currentDate->format('N')),
                ];
            }
            $currentDate->modify('+1 day');
        }

        return $studyDates;
    }

    private function makeStudyBoundaryDate($date)
    {
        return date('Y年m月d日', strtotime($date));
    }

    private function makeChineseWeekDays($weekDays)
    {
        $chineseWeekdays = [];
        foreach ($weekDays as $weekDay) {
            $chineseWeekday = $this->convertChineseWeekDay($weekDay);
            if (!empty($chineseWeekday)) {
                $chineseWeekdays[] = $chineseWeekday;
            }
        }

        return implode('、', $chineseWeekdays);
    }

    private function convertChineseWeekDay($weekday)
    {
        $weekdayMap = [
            1 => '周一',
            2 => '周二',
            3 => '周三',
            4 => '周四',
            5 => '周五',
            6 => '周六',
            7 => '周日'
        ];

        return $weekdayMap[$weekday] ?? '';
    }

    private function findToLearnTasks($courseId)
    {
        $taskResults = $this->getTaskResultService()->findUserFinishedTaskResultsByCourseId($courseId);
        $conditions = ['fromCourseId' => $courseId, 'status' => 'published'];
        if (!empty($taskResults)) {
            $conditions['excludeIds'] = array_column($taskResults, 'activityId');
        }
        $activities = $this->getActivityService()->search(
            $conditions,
            [],
            0,
            PHP_INT_MAX['id']
        );
        if (empty($activities)) {
            return [];
        }
        $activities = $this->getActivityService()->findActivities(array_column($activities, 'id'), true);
        $activities = array_column($activities, null, 'id');
        $tasks = $this->getTaskService()->searchTasks(['courseId' => $courseId, 'status' => 'published', 'activityIds' => array_column($activities, 'id')], ['number' => 'ASC'], 0, count($activities), ['id', 'activityId', 'title']);
        foreach ($tasks as &$task) {
            $activity = $activities[$task['activityId']];
            $task['learnTime'] = CalculationStrategyFactory::create($activity)->calculateTime($activity);
        }

        return $tasks;
    }

    private function makeTable($courseId, $studyDates, $tasks)
    {
        $learnHour = intval(array_sum(array_column($tasks, 'learnTime')) / 3600);
        $studyDateCount = count($studyDates);
        $everytimeLearnHour = round($learnHour / $studyDateCount, 1);
        $everytimeLearnSecond = intval(array_sum(array_column($tasks, 'learnTime')) / $studyDateCount);
        $dateSeq = 0;
        $taskSeq = 0;
        while ($dateSeq < $studyDateCount) {
            $learnSecond = $everytimeLearnSecond;
            while ($learnSecond > 0) {
                $studyDates[$dateSeq]['tasks'] = $studyDates[$dateSeq]['tasks'] ?? [];
                $studyDates[$dateSeq]['tasks'][] = "[{$tasks[$taskSeq]['title']}](/course/{$courseId}/task/{$tasks[$taskSeq]['id']})";
                if ($learnSecond < $tasks[$taskSeq]['learnTime']) {
                    $tasks[$taskSeq]['learnTime'] -= $learnSecond;
                    $learnSecond = 0;
                } else {
                    $learnSecond -= $tasks[$taskSeq]['learnTime'];
                    $taskSeq++;
                }
            }
            $dateSeq++;
        }
        $table = "| 日期 | 学习内容 | 每日学习 |\n| - | - | - |\n";
        foreach ($studyDates as $studyDate) {
            $studyDateTasks = implode('<br>', $studyDate['tasks']);
            $table .= "| {$studyDate['date']}（{$studyDate['weekday']}） | {$studyDateTasks} |  {$everytimeLearnHour}小时 |\n";
        }

        return $table;
    }
}
