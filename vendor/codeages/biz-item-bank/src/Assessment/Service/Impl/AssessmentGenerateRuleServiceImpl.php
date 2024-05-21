<?php

namespace Codeages\Biz\ItemBank\Assessment\Service\Impl;

use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentGenerateRuleDao;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentGenerateRuleService;
use Codeages\Biz\ItemBank\BaseService;

class AssessmentGenerateRuleServiceImpl extends BaseService implements AssessmentGenerateRuleService
{
    public function getAssessmentGenerateRuleByAssessmentId($id)
    {
        return $this->getAssessmentGenerateRuleDao()->getByAssessmentId($id);
    }

    public function createAssessmentGenerateRule($assessmentGenerateRule)
    {
        return $this->getAssessmentGenerateRuleDao()->create($assessmentGenerateRule);
    }

    /**
     * @return AssessmentGenerateRuleDao
     */
    protected function getAssessmentGenerateRuleDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentGenerateRuleDao');
    }
}
