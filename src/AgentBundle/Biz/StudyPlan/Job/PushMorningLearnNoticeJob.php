<?php

namespace AgentBundle\Biz\StudyPlan\Job;

use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use AppBundle\Common\ArrayToolkit;
use Biz\AppPush\Service\AppPushService;
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
        $details = $this->getStudyPlanService()->searchPlanDetails(['studyDate' => date('Y-m-d'), 'courseIds' => array_column($agentConfigs, 'courseId'), 'learned' => 0], [], 0, PHP_INT_MAX);
        $plans = $this->getStudyPlanService()->findPlansByIds(array_column($details, 'planId'));
        $plans = array_column($plans, null, 'id');
        $detailsGroup = ArrayToolkit::group($details, 'courseId');
        foreach ($detailsGroup as $courseId => $planDetails) {
            $this->getAppPushService()->sendToUsers($this->findUserIds($plans, $planDetails), [
                'title' => '小知老师提醒你来学',
                'message' => '今日挑战等你来完成，快来学习啊~',
                'category' => 'todo',
                'extra' => [
                    'domainId' => $agentConfigs[$courseId]['domainId'],
                    'to' => 'ai',
                ],
            ]);
        }
    }

    private function findUserIds($plans, $planDetails)
    {
        $userIds = [];
        foreach ($planDetails as $planDetail) {
            $userIds[] = $plans[$planDetail['planId']]['userId'];
        }

        return $userIds;
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
     * @return AppPushService
     */
    private function getAppPushService()
    {
        return $this->biz->service('AppPush:AppPushService');
    }
}
