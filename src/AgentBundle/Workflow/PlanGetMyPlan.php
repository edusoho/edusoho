<?php

namespace AgentBundle\Workflow;

use AppBundle\Common\ArrayToolkit;

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
        $planTasks = $this->getStudyPlanService()->searchPlanTasks(['planId' => $plan['id'], 'learned' => 0], ['studyDate' => 'ASC'], 0, PHP_INT_MAX);

        return [
            'ok' => true,
            'outputs' => [
                'content' => $this->makeMarkdown($plan, $planTasks),
            ],
        ];
    }

    private function makeMarkdown($plan, $planTasks)
    {
        $course = $this->getCourseService()->getCourse($plan['courseId']);
        $taskIds = array_values(array_unique(array_column($planTasks, 'taskId')));
        $taskCount = count($taskIds);
        $totalLearnDuration = array_sum(array_column($planTasks, 'targetDuration')) - array_sum(array_column($planTasks, 'learnedDuration'));
        $taskGroupByStudyDate = ArrayToolkit::group($planTasks, 'studyDate');
        $studyDateCount = count($taskGroupByStudyDate);
        $everyLearnDuration = empty($plan['dailyAvgTime']) ? round($totalLearnDuration / $studyDateCount) : ($plan['dailyAvgTime'] * 60);
        $tasks = $this->getTaskService()->findTasksByIds($taskIds);
        $tasks = array_column($tasks, null, 'id');

        return <<<MARKDOWN
根据你的学习情况智能调整后，最新学习计划如下：  
1、学习内容：{$course['courseSetTitle']}，共{$taskCount}个任务，学完需要{$this->convertSecondsToCN($totalLearnDuration)}  
2、学习时间：从今日起 至 {$this->convertDateToCN(end($planTasks)['studyDate'])} 内，每{$this->makeChineseWeekDays($plan['weekDays'])}，共计{$studyDateCount}个学习日  
3、每次至少学习：{$this->convertSecondsToCN($everyLearnDuration)}  
我会在剩下的每个学习日提醒你完成学习，期待你的参与！

{$this->makeList($taskGroupByStudyDate, $tasks)}
点击「学习内容」链接直达任务。
MARKDOWN;
    }

    private function makeList($taskGroupByStudyDate, $tasks)
    {
        $list = '';
        $seq = 1;
        foreach ($taskGroupByStudyDate as $studyDate => $planTasks) {
            $list .= '### ' . date('Y/m/d', strtotime($studyDate)) . " {$this->convertWeekDayToCN(date('N', strtotime($studyDate)))}\n\n";
            foreach ($planTasks as $planTask) {
                $task = $tasks[$planTask['taskId']];
                $list .= "* [任务{$seq}: {$task['title']}](/course/{$task['courseId']}/task/{$task['id']})  \n<span class='usetime'>用时・{$this->convertSecondsToCN($planTask['targetDuration'] - $planTask['learnedDuration'])}</span>\n\n\n";
                $seq++;
            }
        }

        return $list;
    }
}
