<?php

namespace AgentBundle\Api\Resource\StudyPlan;

use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class StudyPlanConfig extends AbstractResource
{
    public function add(ApiRequest $request, $operate)
    {
        $params = $request->request->all();
        $this->$operate($params);
        return ['ok' => 'true'];
    }

    private function enable($params)
    {
        $this->getStudyPlanService()->enable($params);
    }

    private function disable($courseId)
    {
        $this->getStudyPlanService()->disable($courseId);
    }

    /**
     * @return StudyPlanService
     */
    private function getStudyPlanService()
    {
        return $this->service('AgentBundle:StudyPlan:StudyPlanService');
    }
}
