<?php

namespace Codeages\Biz\ItemBank\Assessment\Service;

interface AssessmentGenerateRuleService
{
    public function getAssessmentGenerateRuleByAssessmentId($id);

    public function createAssessmentGenerateRule($assessmentGenerateRule);
    
    public function findAssessmentGenerateRuleByAssessmentIds($assessmentIds);

    public function search($conditions, $orderBy, $start, $limit);

    public function updateAssessmentGenerateRuleById($id, $assessmentGenerateRuleParams);
}
