<?php

namespace AgentBundle\Api\Resource\Domain;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\AI\Service\AIService;
use Biz\Course\Service\CourseService;

class DomainMatch extends AbstractResource
{
    public function add(ApiRequest $request, $courseId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $inspectResult = $this->getAIService()->inspectTenant();
        if ('ok' != $inspectResult['status']) {
            return [
                'id' => '',
            ];
        }
        $domains = $this->getAIService()->findDomains('vt');
        $course = $this->getCourseService()->getCourse($courseId);
        $result = $this->getAIService()->runWorkflow('domain.match.vt', [
            'title' => $course['courseSetTitle'],
            'domains' => $domains,
        ]);

        return [
            'id' => $result['outputs']['id'] ?? '',
        ];
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
