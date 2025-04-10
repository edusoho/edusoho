<?php

namespace AgentBundle\Biz\StudyPlan\Job;

use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use Biz\AI\Service\AIService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class PushEveningLearnNoticeJob extends AbstractJob
{
    public function execute()
    {
        $agentConfigs = $this->getAgentConfigService()->findActiveAgentConfigs();
        if (empty($agentConfigs)) {
            return;
        }
        $agentConfigs = array_column($agentConfigs, null, 'courseId');
        $details = $this->getStudyPlanService()->searchPlanDetails(['studyDate' => date('Y-m-d'), 'courseIds' => array_column($agentConfigs, 'courseId'), 'learned' => 0], [], 0, PHP_INT_MAX);
        $plans = $this->getStudyPlanService()->findPlansByIds(array_column($details, 'planId'));
        $plans = array_column($plans, null, 'id');
        $users = $this->getUserService()->findUsersByIds(array_column($plans, 'userId'));
        $tasks = $this->findTasks($details);
        foreach (array_chunk($details, 1000) as $detailsChunk) {
            $params = [];
            foreach ($detailsChunk as $detail) {
                $userId = $plans[$detail['planId']]['userId'];
                $domainId = $agentConfigs[$detail['courseId']]['domainId'];
                $params[] = [
                    'domainId' => $domainId,
                    'userId' => $userId,
                    'contentType' => 'text',
                    'content' => $this->makeMarkdown($users[$userId]['nickname'], array_keys(array_filter($detail['tasks'])), $tasks),
                    'push' => [
                        'userId' => $userId,
                        'title' => '小知老师等你来学',
                        'message' => '很忙吗😥再学一点就能完成今日挑战，点我学习~ ',
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

    private function findTasks($details)
    {
        $taskIds = [];
        foreach ($details as $detail) {
            $taskIds = array_merge($taskIds, array_keys($detail['tasks']));
        }
        $tasks = $this->getTaskService()->findTasksByIds($taskIds);

        return array_column($tasks, null, 'id');
    }

    private function makeMarkdown($nickname, $taskIds, $tasks)
    {
        $markdown = "Hi，{$nickname}同学，忙碌的一天快要结束了，我在等你来学习~  \n";
        foreach ($taskIds as $key => $taskId) {
            $seq = $key + 1;
            $markdown .= "* [任务{$seq}：{$tasks[$taskId]['title']}](/course/{$tasks[$taskId]['courseId']}/task/{$taskId})\n";
        }

        return $markdown;
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
