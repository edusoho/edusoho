<?php

namespace AgentBundle\Biz\StudyPlan\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface StudyPlanDetailDao extends AdvancedDaoInterface
{
    public function getByPlanIdAndStudyDate($planId, $studyDate);
}
