<?php

namespace AgentBundle\Api\Resource\Agent;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\AI\Service\AIService;

class Agent extends AbstractResource
{
    /**
     * @Access(roles="ROLE_TEACHER", permissions="admin_v2")
     */
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
