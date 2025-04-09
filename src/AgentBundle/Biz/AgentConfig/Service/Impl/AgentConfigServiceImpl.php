<?php

namespace AgentBundle\Biz\AgentConfig\Service\Impl;

use AgentBundle\Biz\AgentConfig\Dao\AiStudyConfigDao;
use AgentBundle\Biz\AgentConfig\Exception\AgentConfigException;
use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use AppBundle\Common\ArrayToolkit;
use Biz\AI\Service\AIService;
use Biz\BaseService;
use Biz\Common\CommonException;

class AgentConfigServiceImpl extends BaseService implements AgentConfigService
{
    public function getAgentConfigByCourseId($courseId)
    {
        return $this->getAiStudyConfigDao()->getByCourseId($courseId);
    }

    public function createAgentConfig($params)
    {
        if (!ArrayToolkit::requireds($params, ['courseId', 'name', 'domainId'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $agentConfig = $this->getAgentConfigByCourseId($params['courseId']);
        if (!empty($agentConfig)) {
            throw AgentConfigException::AGENT_CONFIG_ALREADY_CREATED();
        }
        $this->checkDomain($params['domainId']);
        $dataset = $this->getAIService()->createDataset([
            'externalId' => $params['courseId'],
            'name' => $params['name'],
            'domainId' => $params['domainId'],
            'autoIndex' => !empty($params['isDiagnosisActive']),
        ]);
        $agentConfig = $this->getAiStudyConfigDao()->create([
            'courseId' => $params['courseId'],
            'isActive' => 1,
            'datasetId' => $dataset['id'],
            'domainId' => $params['domainId'],
            'planDeadline' => $params['planDeadline'],
            'isDiagnosisActive' => $params['isDiagnosisActive'],
            'indexing' => empty($params['isDiagnosisActive']) ? 0 : 1,
        ]);
        $this->dispatchEvent('agentConfig.create', $agentConfig);

        return $agentConfig;
    }

    public function updateAgentConfig($courseId, $params)
    {
        $agentConfig = $this->getAgentConfigByCourseId($courseId);
        if (empty($agentConfig)) {
            throw AgentConfigException::AGENT_CONFIG_NOT_FOUND();
        }
        $params = ArrayToolkit::parts($params, ['isActive', 'domainId', 'planDeadline', 'isDiagnosisActive']);
        if (!empty($params['domainId'])) {
            $this->checkDomain($params['domainId']);
        }
        if (empty($params['isActive']) || empty($params['isDiagnosisActive'])) {
            $params['indexing'] = 0;
        } elseif (empty($agentConfig['isActive']) || empty($agentConfig['isDiagnosisActive'])) {
            $params['indexing'] = 1;
        }
        $agentConfig = $this->getAiStudyConfigDao()->update($agentConfig['id'], $params);
        $this->getAIService()->updateDataset($agentConfig['datasetId'], ['domainId' => $agentConfig['domainId'], 'autoIndex' => !empty($agentConfig['isDiagnosisActive'])]);
    }

    public function findAgentConfigsByCourseIds($courseIds)
    {
        return $this->getAiStudyConfigDao()->findByCourseIds($courseIds);
    }

    public function findAgentConfigsByDomainId($domainId)
    {
        return $this->getAiStudyConfigDao()->findByDomainId($domainId);
    }

    public function findIndexingAgentConfigs()
    {
        return $this->getAiStudyConfigDao()->findIndexing();
    }

    public function findActiveAgentConfigs()
    {
        return $this->getAiStudyConfigDao()->findActive();
    }

    public function markIndexFinished($id)
    {
        $this->getAiStudyConfigDao()->update($id, ['indexing' => 0]);
    }

    private function checkDomain($domainId)
    {
        $domains = $this->getAIService()->findDomains('vt');
        $domains = array_column($domains, null, 'id');
        if (empty($domains[$domainId])) {
            throw AgentConfigException::UNKNOWN_DOMAIN();
        }
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->createService('AI:AIService');
    }

    /**
     * @return AiStudyConfigDao
     */
    protected function getAiStudyConfigDao()
    {
        return $this->createDao('AgentBundle:AgentConfig:AiStudyConfigDao');
    }
}
