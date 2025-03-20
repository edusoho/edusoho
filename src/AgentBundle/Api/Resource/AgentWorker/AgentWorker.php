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
        if (!ArrayToolkit::requireds($params, ['workflow', 'data'])) {
            return [
                'status' => 'PARAMS_LACK',
                'content' => '',
            ];
        }
        if (empty($this->biz["agent.workflow.{$params['workflow']}"])) {
            return [
                'status' => 'UNKNOWN_WORKFLOW',
                'content' => '',
            ];
        }
        $workflow = $this->biz["agent.workflow.{$params['workflow']}"];

        return $workflow->execute($params['data']);
    }
}
