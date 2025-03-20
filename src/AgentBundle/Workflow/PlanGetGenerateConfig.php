<?php

namespace AgentBundle\Workflow;

class PlanGetGenerateConfig extends AbstractWorkflow
{
    public function execute($data)
    {
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($data['courseId']);
        if (empty($agentConfig['isActive'])) {
            return ['status' => 'AI_DISABLED'];
        }

        return [
            'status' => 'OK',
            'data' => [
                'deadlines' => $agentConfig['planDeadline'],
            ],
        ];
    }
}
