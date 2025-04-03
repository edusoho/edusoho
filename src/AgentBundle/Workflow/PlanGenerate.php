<?php

namespace AgentBundle\Workflow;

use AgentBundle\Biz\StudyPlan\Factory\CalculationStrategyFactory;
use DateTime;

class PlanGenerate extends AbstractWorkflow
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
                    'code' => 'INVALID_ARGUMENT',
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
        $tasks = $this->scheduleTasks($inputs, $tasks);
        $this->getStudyPlanService()->generatePlan([
            'courseId' => $inputs['courseId'],
            'startDate' => empty($inputs['startDate']) ? date('Y-m-d') : $inputs['startDate'],
            'endDate' => empty($inputs['endDate']) ? end($tasks)['date'] : $inputs['endDate'],
            'weekDays' => $inputs['weekDays'],
            'dailyAvgTime' => empty($inputs['dailyLearnDuration']) ? 0 : ($inputs['dailyLearnDuration'] * 60),
        ]);

        return [
            'ok' => true,
            'outputs' => [
                'content' => $this->makeMarkdown($inputs, $tasks),
            ],
        ];
    }

    private function makeMarkdown($inputs, $tasks)
    {
        $course = $this->getCourseService()->getCourse($inputs['courseId']);
        $studyDates = $this->groupTasksByDate($tasks);
        $studyDateCount = count($studyDates);
        $taskCount = count($tasks);
        $learnHour = array_sum(array_column($tasks, 'duration'));
        $startDate = empty($inputs['startDate']) ? '从今日起' : $this->makeStudyBoundaryDate($inputs['startDate']);
        $endDate = empty($inputs['endDate']) ? '学完即止' : $this->makeStudyBoundaryDate($inputs['endDate']);
        $minDuration = min(array_column($studyDates, 'duration'));

        return <<<MARKDOWN
根据上述内容为你生成以下学习计划：  
1、学习内容：{$course['courseSetTitle']}，共{$taskCount}个任务，学完需要{$learnHour}小时  
2、学习时间：{$startDate} 至 {$endDate} 内，每{$this->makeChineseWeekDays($inputs['weekDays'])}，共计{$studyDateCount}个学习日  
3、每次至少学习：{$minDuration}小时  
我会在每个学习日提醒你完成学习，期待你的参与！

{$this->makeList($studyDates)}
点击「学习内容」链接直达任务。
MARKDOWN;
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

    private function makeList($studyDates)
    {
        $list = '';
        foreach ($studyDates as $studyDate => $dateTasks) {
            $list .= '### '.date('Y/m/d', strtotime($studyDate))." {$this->convertChineseWeekDay(date('N', strtotime($studyDate)))}\n\n";
            foreach ($dateTasks['tasks'] as $task) {
                $list .= "* 📄&nbsp; **[{$task['title']}](/course/{$task['courseId']}/task/{$task['id']})**  \n每日学习・{$task['duration']}小时\n\n\n";
            }
        }

        return $list;
    }
}
