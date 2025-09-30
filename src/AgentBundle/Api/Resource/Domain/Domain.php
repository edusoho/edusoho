<?php

namespace AgentBundle\Api\Resource\Domain;

use ApiBundle\Api\Resource\AbstractResource;
use Biz\AI\Service\AIService;

class Domain extends AbstractResource
{
    public function search()
    {
        if (!$this->getCurrentUser()->isTeacher() && !$this->getCurrentUser()->isAdmin()) {
            return [];
        }
        if (!$this->getAIService()->isAgentEnable()) {
            return [];
        }

        return $this->getAIService()->findDomains('vt');
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->biz->service('AI:AIService');
    }
}
