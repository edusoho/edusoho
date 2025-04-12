<?php

namespace AgentBundle\Workflow;

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
        $plan = $this->getStudyPlanService()->generatePlan([
            'courseId' => $inputs['courseId'],
            'startDate' => empty($inputs['startDate']) ? date('Y-m-d') : $inputs['startDate'],
            'endDate' => empty($inputs['endDate']) ? end($tasks)['date'] : $inputs['endDate'],
            'weekDays' => $inputs['weekDays'],
            'dailyAvgTime' => empty($inputs['dailyLearnDuration']) ? 0 : ($inputs['dailyLearnDuration'] * 60),
        ]);
        $studyDates = $this->groupTasksByDate($tasks);
        $this->getStudyPlanService()->createPlanDetails($plan['id'], $studyDates);

        return [
            'ok' => true,
            'outputs' => [
                'content' => $this->makeMarkdown($inputs, $studyDates, $tasks),
            ],
        ];
    }

    private function makeMarkdown($inputs, $studyDates, $tasks)
    {
        $course = $this->getCourseService()->getCourse($inputs['courseId']);
        $studyDateCount = count($studyDates);
        $taskCount = count(array_unique(array_column($tasks, 'id')));
        $totalLearnTime = $this->convertSecondsToCN(array_sum(array_column($tasks, 'duration')));
        $startDate = empty($inputs['startDate']) ? '从今日起' : $this->convertDateToCN($inputs['startDate']);
        $endDate = empty($inputs['endDate']) ? '学完即止' : $this->convertDateToCN($inputs['endDate']);
        $everyLearnTime = $this->convertSecondsToCN(current(array_column($studyDates, 'duration')));

        return <<<MARKDOWN
根据上述内容为你生成以下学习计划，计划内容将会根据学习情况智能调整：  
1、学习内容：{$course['courseSetTitle']}，共{$taskCount}个任务，学完需要{$totalLearnTime}  
2、学习时间：{$startDate} 至 {$endDate} 内，每{$this->makeChineseWeekDays($inputs['weekDays'])}，共计{$studyDateCount}个学习日  
3、每次至少学习：{$everyLearnTime}  
我会在每个学习日提醒你完成学习，期待你的参与！

{$this->makeList($studyDates)}
点击「学习内容」链接直达任务。
MARKDOWN;
    }

    private function makeList($studyDates)
    {
        $list = '';
        $seq = 1;
        foreach ($studyDates as $studyDate => $dateTasks) {
            $list .= '### '.date('Y/m/d', strtotime($studyDate))." {$this->convertWeekDayToCN(date('N', strtotime($studyDate)))}\n\n";
            foreach ($dateTasks['tasks'] as $task) {
                $list .= "* [任务{$seq}: {$task['title']}](/course/{$task['courseId']}/task/{$task['id']})  \n<span class='usetime'>用时・{$this->convertSecondsToCN($task['duration'])}</span>\n\n\n";
                $seq++;
            }
        }

        return $list;
    }
}
