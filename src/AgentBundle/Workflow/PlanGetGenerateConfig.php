<?php

namespace AgentBundle\Workflow;

class PlanGetGenerateConfig extends AbstractWorkflow
{
    public function execute($data)
    {
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($data['courseId']);
        if (empty($agentConfig['isActive'])) {
            return [
                'ok' => false,
                'error' => [
                    'code' => 'AI_DISABLED',
                    'message' => '伴学服务未开启',
                ],
            ];
        }

        return [
            'ok' => true,
            'outputs' => [
                'deadlines' => $agentConfig['planDeadline'],
            ],
        ];
    }
}
