<?php

namespace AgentBundle\Biz\StudyPlan\Service;

interface StudyPlanService
{
    public function generate($startTime, $endTime, $studyDays, $courseId);
}
