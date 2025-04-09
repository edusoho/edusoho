<?php

namespace AgentBundle\Biz\StudyPlan\Service;

interface StudyPlanService
{
    public function createConfig($aiStudyConfig);

    public function updateConfig($aiStudyConfig);

    public function getGenerateConfig($data);

    public function generate($params);

    public function generatePlan($data);

    public function createPlanDetails($planId, $studyDates);

    public function searchPlanDetails($conditions, $orderBys, $start, $limit, $columns = []);

    public function findPlansByIds($ids);

    public function getPlanByCourseIdAndUserId($courseId, $userId);

    public function isUserStudyPlanGenerated($userId, $courseId);

    public function getPlanDetailByPlanIdAndStudyDate($planId, $studyDate);

    public function updatePlanDetailTasks($id, $tasks);

    public function updatePlanDetailLearned($ids);
}
