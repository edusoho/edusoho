<?php

namespace AgentBundle\Workflow;

class PlanGetMyPlan extends AbstractWorkflow
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
        $plan = $this->getStudyPlanService()->getPlanByCourseIdAndUserId($inputs['courseId'], $this->biz['user']['id']);
        if (empty($plan)) {
            return [
                'ok' => true,
                'outputs' => [
                    'content' => '当前课程，您尚未生成学习计划。',
                ],
            ];
        }
        $details = $this->getStudyPlanService()->searchPlanDetails(['planId' => $plan['id'], 'learned' => 0], ['studyDate' => 'ASC'], 0, PHP_INT_MAX);

        return [
            'ok' => true,
            'outputs' => [
                'content' => $this->makeMarkdown($plan, $details),
            ],
        ];
    }

    private function makeMarkdown($plan, $details)
    {
        $course = $this->getCourseService()->getCourse($plan['courseId']);
        $studyDateCount = count($details);
        $taskIds = [];
        $totalLearnDuration = 0;
        $everyLearnDuration = 0;
        foreach ($details as $detail) {
            $taskIds = array_merge($taskIds, array_keys($detail['tasks']));
            $totalLearnDuration += array_sum(array_values($detail['tasks']));
            $everyLearnDuration = max($everyLearnDuration, array_sum(array_values($detail['tasks'])));
        }
        $taskCount = count(array_unique($taskIds));

        return <<<MARKDOWN
根据你的学习情况智能调整后，最新学习计划如下：  
1、学习内容：{$course['courseSetTitle']}，共{$taskCount}个任务，学完需要{$this->convertSecondsToCN($totalLearnDuration)}  
2、学习时间：从今日起 至 {$this->convertDateToCN($plan['endDate'])} 内，每{$this->makeChineseWeekDays($plan['weekDays'])}，共计{$studyDateCount}个学习日  
3、每次至少学习：{$this->convertSecondsToCN($everyLearnDuration)}  
我会在剩下的每个学习日提醒你完成学习，期待你的参与！

{$this->makeList($details)}
点击「学习内容」链接直达任务。
MARKDOWN;
    }

    private function findTasks($details)
    {
        $taskIds = [];
        foreach ($details as $detail) {
            $taskIds = array_merge($taskIds, array_keys($detail['tasks']));
        }
        $tasks = $this->getTaskService()->findTasksByIds($taskIds);

        return array_column($tasks, null, 'id');
    }

    private function makeList($details)
    {
        $tasks = $this->findTasks($details);
        $list = '';
        $seq = 1;
        foreach ($details as $detail) {
            $list .= '### ' . date('Y/m/d', strtotime($detail['studyDate'])) . " {$this->convertWeekDayToCN(date('N', strtotime($detail['studyDate'])))}\n\n";
            foreach ($detail['tasks'] as $taskId => $duration) {
                $task = $tasks[$taskId];
                $list .= "* [任务{$seq}: {$task['title']}](/course/{$task['courseId']}/task/{$task['id']})  \n<span class='usetime'>用时・{$this->convertSecondsToCN($duration)}</span>\n\n\n";
                $seq++;
            }
        }

        return $list;
    }
}
