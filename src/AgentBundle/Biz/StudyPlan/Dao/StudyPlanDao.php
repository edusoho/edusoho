<?php

namespace AgentBundle\Biz\StudyPlan\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface StudyPlanDao extends GeneralDaoInterface
{
    public function getStudyPlanByUserIdAndCourseId($userId, $courseId);
}
