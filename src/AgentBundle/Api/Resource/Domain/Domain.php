<?php

namespace AgentBundle\Api\Resource\Domain;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\AI\Service\AIService;
use Biz\Course\Service\CourseService;

class Domain extends AbstractResource
{
    public function get(ApiRequest $request, $courseId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        if (!$this->getAIService()->isAgentEnable()) {
            return [];
        }

        return $this->getAIService()->findDomains('vt');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->biz->service('AI:AIService');
    }
}
