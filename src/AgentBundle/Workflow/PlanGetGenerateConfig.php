<?php

namespace AgentBundle\Workflow;

class PlanGetGenerateConfig extends AbstractWorkflow
{
    use TaskTrait;

    public function execute($inputs)
    {
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($inputs['courseId']);
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
                'deadlines' => $this->filterDeadlines($agentConfig['planDeadline']),
                'canGenerate' => $this->canGenerate($inputs['courseId']),
            ],
        ];
    }

    private function filterDeadlines($deadlines)
    {
        return array_values(array_filter($deadlines, function ($deadline) {
            return $deadline >= date('Y-m-d');
        }));
    }

    private function canGenerate($courseId)
    {
        $tasks = $this->findSchedulableTasks($courseId);

        return !empty($tasks);
    }
}
