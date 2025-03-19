<?php

namespace AgentBundle\Biz\AgentConfig\Service;

interface AgentConfigService
{
    public function getAgentConfigByCourseId($courseId);

    public function createAgentConfig($params);

    public function updateAgentConfig($courseId, $params);
}
