<?php

namespace Codeages\Biz\ItemBank\Assessment\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AssessmentSectionItemDao extends AdvancedDaoInterface
{
    public function findByAssessmentId($assessmentId);

    public function deleteByAssessmentId($assessmentId);

    public function getByAssessmentIdAndItemId($assessmentId, $itemId);
}
