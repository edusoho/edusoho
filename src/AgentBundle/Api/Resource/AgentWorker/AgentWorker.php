<?php

namespace AgentBundle\Api\Resource\AgentWorker;

use AgentBundle\Executor\CommandExecutor;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class AgentWorker extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        $commandExecutor = new CommandExecutor();

        return $commandExecutor->execute($params['workflow'], $params['data']);
    }
}
