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
    public function createAgentConfig($params)
    {
        if (!ArrayToolkit::requireds($params, ['courseId', 'name', 'domainId'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $agentConfig = $this->getAgentConfigByCourseId($params['courseId']);
        if (!empty($agentConfig)) {
            throw AgentConfigException::AGENT_CONFIG_ALREADY_CREATED();
        }
        $domains = $this->getAIService()->findDomains('vt');
        $domains = array_column($domains, null, 'id');
        if (empty($domains[$params['domainId']])) {
            throw AgentConfigException::UNKNOWN_DOMAIN();
        }
        $dataset = $this->getAIService()->createDataset([
            'externalId' => $params['courseId'],
            'name' => $params['name'],
            'domainId' => $params['domainId'],
            'autoIndex' => !empty($params['isDiagnosisActive']),
        ]);

        return $this->getAiStudyConfigDao()->create([
            'courseId' => $params['courseId'],
            'isActive' => 1,
            'datasetId' => $dataset['id'],
            'domainId' => $params['domainId'],
            'planDeadline' => $params['planDeadline'],
            'isDiagnosisActive' => $params['isDiagnosisActive'],
        ]);
    }

    public function getAgentConfig($id)
    {
        return $this->getAiStudyConfigDao()->get($id);
    }

    public function getAgentConfigByCourseId($courseId)
    {
        return $this->getAiStudyConfigDao()->getAiStudyConfigByCourseId($courseId);
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
