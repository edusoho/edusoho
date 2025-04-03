<?php

namespace AgentBundle\Biz\AgentConfig\Job;

use AgentBundle\Biz\AgentConfig\Constant\IndexStatus;
use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use Biz\AI\Service\AIService;
use Biz\Course\Service\CourseService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class NotifyDatasetIndexStatusJob extends AbstractJob
{
    public function execute()
    {
        $agentConfigs = $this->getAgentConfigService()->findIndexingAgentConfigs();
        foreach ($agentConfigs as $agentConfig) {
            $dataset = $this->getAIService()->getDataset($agentConfig['datasetId']);
            if (empty($dataset)) {
                continue;
            }
            if (empty($dataset['indexing'])) {
                $this->getAgentConfigService()->markIndexFinished($agentConfig['id']);
                $course = $this->getCourseService()->getCourse($agentConfig['courseId']);
                $this->getNotificationService()->batchNotify($course['teacherIds'], 'dataset-index', [
                    'courseSetId' => $course['courseSetId'],
                    'courseSetTitle' => $course['courseSetTitle'],
                    'courseId' => $course['id'],
                    'status' => empty($dataset['failedCount']) ? IndexStatus::SUCCESS : IndexStatus::FAILED,
                ]);
            }
        }
    }

    /**
     * @return AgentConfigService
     */
    private function getAgentConfigService()
    {
        return $this->biz->service('AgentBundle:AgentConfig:AgentConfigService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return NotificationService
     */
    private function getNotificationService()
    {
        return $this->biz->service('User:NotificationService');
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->biz->service('AI:AIService');
    }
}
