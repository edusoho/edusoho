<?php

namespace AgentBundle\Biz\AgentConfig\Service;

interface AgentConfigService
{
    public function createAgentConfig($params);

    public function getAgentConfig($id);

    public function getAgentConfigByCourseId($courseId);
}
