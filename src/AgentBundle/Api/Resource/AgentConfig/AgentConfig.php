<?php

namespace AgentBundle\Api\Resource\AgentConfig;

use AgentBundle\Biz\AgentConfig\Constant\IndexStatus;
use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\AI\Service\AIService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;

class AgentConfig extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        if (empty($params['courseId'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $this->getCourseService()->tryManageCourse($params['courseId']);
        $course = $this->getCourseService()->getCourse($params['courseId']);
        $params['name'] = $course['courseSetTitle'];
        $agentConfig = $this->getAgentConfigService()->createAgentConfig($params);

        return ['id' => $agentConfig['id']];
    }

    public function get(ApiRequest $request, $courseId)
    {
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($courseId);
        $agentConfig = empty($agentConfig) ? ['isActive' => 0] : $agentConfig;
        $inspectResult = $this->getAIService()->inspectTenant();
        if (('ok' != $inspectResult['status']) || !in_array('agent', $inspectResult['permissions'])) {
            $agentConfig['agentEnable'] = false;
        } else {
            $agentConfig['agentEnable'] = true;
        }
        if (!empty($agentConfig['isDiagnosisActive'])) {
            $dataset = $this->getAIService()->getDataset($agentConfig['datasetId']);
            $agentConfig['indexStatus'] = $this->getIndexStatus($dataset);
            $agentConfig['indexProgress'] = $this->calIndexProgress($dataset);
        }

        return $agentConfig;
    }

    public function update(ApiRequest $request, $courseId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $this->getAgentConfigService()->updateAgentConfig($courseId, $request->request->all());

        return ['ok' => true];
    }

    private function getIndexStatus($dataset)
    {
        if ($dataset['successCount'] == $dataset['totalCount']) {
            return IndexStatus::SUCCESS;
        }
        if ($dataset['failedCount'] > 0) {
            return IndexStatus::FAILED;
        }

        return IndexStatus::INDEXING;
    }

    private function calIndexProgress($dataset)
    {
        if (empty($dataset['totalCount'])) {
            return 100;
        }

        return intval($dataset['successCount'] / $dataset['totalCount']);
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return AgentConfigService
     */
    private function getAgentConfigService()
    {
        return $this->service('AgentBundle:AgentConfig:AgentConfigService');
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->service('AI:AIService');
    }
}
