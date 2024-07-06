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
        $this->getValidator()->validate($assessmentGenerateRule, [
            'assessment_id' => 'required',
            'num' => ['integer', ['min', 0], ['max', 200]],
            'type' => [['in', ['questionType', 'questionTypeCategory']]],
        ]);
        return $this->getAssessmentGenerateRuleDao()->create($assessmentGenerateRule);
    }

    public function findAssessmentGenerateRuleByAssessmentIds($assessmentIds)
    {
        return $this->getAssessmentGenerateRuleDao()->findByAssessmentIds($assessmentIds);
    }

    public function search($conditions, $orderBy, $start, $limit)
    {
        return $this->getAssessmentGenerateRuleDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function updateAssessmentGenerateRuleById($id, $assessmentGenerateRuleParams)
    {
        return $this->getAssessmentGenerateRuleDao()->update($id, $assessmentGenerateRuleParams);
    }

    /**
     * @return AssessmentGenerateRuleDao
     */
    protected function getAssessmentGenerateRuleDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentGenerateRuleDao');
    }
}
