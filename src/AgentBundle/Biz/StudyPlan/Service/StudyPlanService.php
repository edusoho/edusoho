<?php

namespace AgentBundle\Biz\StudyPlan\Service;

interface StudyPlanService
{
    public function createConfig($aiStudyConfig);

    public function updateConfig($aiStudyConfig);

    public function getGenerateConfig($data);

    public function generate($params);

    public function generatePlan($data);

    public function isUserStudyPlanGenerated($userId, $courseId);
}
