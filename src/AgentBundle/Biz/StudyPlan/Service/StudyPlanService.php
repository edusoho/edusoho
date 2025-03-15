<?php

namespace AgentBundle\Biz\StudyPlan\Service;

interface StudyPlanService
{
    public function enable($aiStudyConfig);

    public function disable($aiStudyConfig);

    public function generate($startTime, $endTime, $studyDays, $courseId);
}
