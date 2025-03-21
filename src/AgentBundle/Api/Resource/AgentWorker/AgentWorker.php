<?php

namespace AgentBundle\Api\Resource\AgentWorker;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;

class AgentWorker extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        if (!ArrayToolkit::requireds($params, ['workflow', 'inputs'])) {
            return [
                'ok' => false,
                'error' => [
                    'code' => 'PARAMS_LACK',
                    'message' => '参数缺失',
                ],
            ];
        }
        if (empty($this->biz["agent.workflow.{$params['workflow']}"])) {
            return [
                'ok' => false,
                'error' => [
                    'code' => 'UNKNOWN_WORKFLOW',
                    'message' => '未知工作流',
                ],
            ];
        }
        $workflow = $this->biz["agent.workflow.{$params['workflow']}"];

        return $workflow->execute($params['inputs']);
    }
}
