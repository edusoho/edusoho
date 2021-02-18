<?php

namespace Codeages\Biz\ItemBank\Assessment\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AssessmentSectionDao extends GeneralDaoInterface
{
    public function findByAssessmentId($assessmentId);

    public function deleteByAssessmentId($assessmentId);
}
