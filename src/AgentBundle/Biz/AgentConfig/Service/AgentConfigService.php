<?php

namespace AgentBundle\Biz\AgentConfig\Service;

interface AgentConfigService
{
    public function getAgentConfigByCourseId($courseId);

    public function createAgentConfig($params);

    public function updateAgentConfig($courseId, $params);

    public function deleteAgentConfig($id);

    public function findAgentConfigsByCourseIds($courseIds);

    public function findAgentConfigsByDomainId($domainId);

    public function findIndexingAgentConfigs();

    public function findActiveAgentConfigs();

    public function markIndexFinished($id);
}
