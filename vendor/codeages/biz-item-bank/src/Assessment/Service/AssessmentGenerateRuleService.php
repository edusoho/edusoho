<?php

namespace Codeages\Biz\ItemBank\Assessment\Service;

interface AssessmentGenerateRuleService
{
    public function getAssessmentGenerateRuleByAssessmentId($id);

    public function createAssessmentGenerateRule($assessmentGenerateRule);
}
