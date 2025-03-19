<?php

namespace AgentBundle\Api\Resource\AgentConfig;

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
        $params['name'] = '默认计划' == $course['title'] ? $course['courseSetTitle'] : "{$course['courseSetTitle']}-{$course['title']}";
        $agentConfig = $this->getAgentConfigService()->createAgentConfig($params);

        return ['id' => $agentConfig['id']];
    }

    public function get(ApiRequest $request, $courseId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($courseId);
        $agentConfig = empty($agentConfig) ? ['isActive' => 0] : $agentConfig;
//        $this->getAIService()->disableTenant();
        $this->getAIService()->enableTenant();
        $inspectResult = $this->getAIService()->inspectTenant();
        if (('ok' != $inspectResult['status']) || !in_array('agent', $inspectResult['permissions'])) {
            $agentConfig['agentEnable'] = false;
        } else {
            $agentConfig['agentEnable'] = true;
        }

        return $agentConfig;
    }

    public function update(ApiRequest $request, $id)
    {
        $agentConfig = $this->getAgentConfigService()->getAgentConfig($id);

        return ['ok' => true];
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
        return $this->biz->service('AgentBundle:AgentConfig:AgentConfigService');
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->biz->service('AI:AIService');
    }
}
