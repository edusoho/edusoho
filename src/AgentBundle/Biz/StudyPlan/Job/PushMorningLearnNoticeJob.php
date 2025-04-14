<?php

namespace AgentBundle\Biz\StudyPlan\Job;

use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use AppBundle\Common\ArrayToolkit;
use Biz\AI\Service\AIService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class PushMorningLearnNoticeJob extends AbstractJob
{
    public function execute()
    {
        $agentConfigs = $this->getAgentConfigService()->findActiveAgentConfigs();
        if (empty($agentConfigs)) {
            return;
        }
        $agentConfigs = array_column($agentConfigs, null, 'courseId');
        $courseIds = array_column($agentConfigs, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $planTasks = $this->getStudyPlanService()->searchPlanTasks(['studyDate' => date('Y-m-d'), 'courseIds' => $courseIds, 'learned' => 0], [], 0, PHP_INT_MAX);
        $plans = $this->getStudyPlanService()->findActivePlansByIds(array_column($planTasks, 'planId'));
        $users = $this->getUserService()->findUsersByIds(array_column($plans, 'userId'));
        $tasks = $this->getTaskService()->findTasksByIds(array_column($planTasks, 'taskId'));
        $tasks = array_column($tasks, null, 'id');
        $planTasksGroup = ArrayToolkit::group($planTasks, 'planId');
        foreach (array_chunk($plans, 1000) as $plansChunk) {
            $params = [];
            foreach ($plansChunk as $plan) {
                $domainId = $agentConfigs[$plan['courseId']]['domainId'];
                $params[] = [
                    'domainId' => $domainId,
                    'userId' => $plan['userId'],
                    'contentType' => 'text',
                    'content' => $this->makeMarkdown($users[$plan['userId']]['nickname'], $courses[$plan['courseId']]['courseSetTitle'], $planTasksGroup[$plan['id']], $tasks),
                    'push' => [
                        'userId' => $plan['userId'],
                        'title' => '小知老师提醒你来学',
                        'message' => '今日挑战等你来完成，快来学习啊~',
                        'category' => 'todo',
                        'extra' => [
                            'domainId' => $domainId,
                            'to' => 'ai',
                        ],
                    ],
                ];
            }
            $this->getAIService()->batchPushMessage($params);
        }
    }

    private function makeMarkdown($nickname, $courseTitle, $planTasks, $tasks)
    {
        $markdown = "Hi，{$nickname}同学，今日你将学习《{$courseTitle}》，这是我为你规划的今日学习内容：  \n";
        foreach ($planTasks as $key => $planTask) {
            $seq = $key + 1;
            $markdown .= "* [任务{$seq}：{$tasks[$planTask['taskId']]['title']}](/course/{$tasks[$planTask['taskId']]['courseId']}/task/{$planTask['taskId']})\n";
        }

        return $markdown;
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return AgentConfigService
     */
    private function getAgentConfigService()
    {
        return $this->biz->service('AgentBundle:AgentConfig:AgentConfigService');
    }

    /**
     * @return StudyPlanService
     */
    private function getStudyPlanService()
    {
        return $this->biz->service('AgentBundle:StudyPlan:StudyPlanService');
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->biz->service('AI:AIService');
    }
}
