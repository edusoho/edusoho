<?php

namespace AgentBundle\Api\Resource\Agent;

use ApiBundle\Api\Resource\AbstractResource;
use Biz\AI\Service\AIService;

class Agent extends AbstractResource
{
    public function get()
    {
        return [
            'enable' => $this->getAIService()->isAgentEnable(),
        ];
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->service('AI:AIService');
    }
}
