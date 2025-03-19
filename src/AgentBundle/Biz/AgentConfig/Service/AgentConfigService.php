<?php

namespace AgentBundle\Biz\AgentConfig\Service;

interface AgentConfigService
{
    public function createAgentConfig($params);

    public function getAgentConfigByCourseId($courseId);
}
