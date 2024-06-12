<?php

namespace Codeages\Biz\ItemBank\Assessment\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AssessmentGenerateRuleDao extends AdvancedDaoInterface
{
    public function getByAssessmentId($assessmentId);
}
