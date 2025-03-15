<?php

namespace AgentBundle\Biz\StudyPlan\Service;

interface StudyPlanService
{
    public function enable($aiStudyConfig);

    public function disable($courseId);

    public function getGenerateConfig($data);

    public function generate($startTime, $endTime, $studyDays, $courseId);
}
