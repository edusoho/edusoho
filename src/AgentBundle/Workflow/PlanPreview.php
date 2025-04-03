<?php

namespace AgentBundle\Workflow;

class PlanPreview extends AbstractWorkflow
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
        if (empty($inputs['endDate']) && empty($inputs['dailyLearnDuration'])) {
            return [
                'ok' => false,
                'error' => [
                    'code' => 'INVALID_ARGUMENT',
                    'message' => 'endDate和dailyLearnDuration不能都为空',
                ],
            ];
        }
        $tasks = $this->findSchedulableTasks($inputs['courseId']);
        if (empty($tasks)) {
            return [
                'ok' => false,
                'error' => [
                    'code' => 'NO_LESSON_CAN_PLAN',
                    'message' => '无法制定学习计划',
                ],
            ];
        }

        return [
            'ok' => true,
            'outputs' => [
                'tasks' => $this->scheduleTasks($inputs, $tasks),
            ],
        ];
    }
}
