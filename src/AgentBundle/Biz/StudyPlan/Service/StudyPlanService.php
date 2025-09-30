<?php

namespace AgentBundle\Biz\StudyPlan\Service;

interface StudyPlanService
{
    public function generatePlan($data);

    public function deletePlan($id);

    public function generatePlanTasks($planId, $tasks);

    public function deletePlanTasksByPlanId($planId);

    public function searchPlanTasks($conditions, $orderBys, $start, $limit, $columns = []);

    public function findActivePlansByIds($ids);

    public function findActivePlansByCourseId($courseId);

    public function getPlanByCourseIdAndUserId($courseId, $userId);

    public function isUserStudyPlanGenerated($userId, $courseId);

    public function wavePlanTaskLearnedDuration($id, $increment);

    public function markPlanTaskLearned($ids);
}
