<?php

namespace ApiBundle\Api\Resource\Assessment;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentGenerateRuleService;

class AssessmentGenerateTemplate extends AbstractResource
{
    public function search(ApiRequest $request, $type)
    {
        if (in_array($type, ['questionType', 'questionTypeCategory'])) {
            throw CommonException::ERROR_PARAMETER();
        }

        return $this->getAssessmentGenerateRuleService()->search(['type' => $type], ['created_time' => 'desc'], 0, 1);
    }

    /**
     * @return AssessmentGenerateRuleService
     */
    private function getAssessmentGenerateRuleService()
    {
        return $this->service('ItemBank:Assessment:AssessmentGenerateRuleService');
    }
}
