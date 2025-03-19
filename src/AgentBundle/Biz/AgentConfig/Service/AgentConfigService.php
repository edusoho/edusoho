<?php

namespace AgentBundle\Biz\AgentConfig\Service;

interface AgentConfigService
{
    public function getAgentConfigByCourseId($courseId);

    public function createAgentConfig($params);

    public function getAgentConfig($id);

    public function updateAgentConfig($id, $params);
}
