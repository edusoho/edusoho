<?php

namespace AgentBundle\Biz\AgentConfig\Job;

use Biz\AI\Service\AIService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class InspectAITenantJob extends AbstractJob
{
    public function execute()
    {
        try {
            $this->getAIService()->inspectTenant();
        } catch (\Exception $exception) {
            $this->biz['logger']->error('inspect ai tenant error: ' . $exception->getMessage());
        }
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->biz->service('AI:AIService');
    }
}
