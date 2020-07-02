<?php

namespace Biz\ItemBankExercise\Service;

interface AssessmentExerciseService
{
    public function findByModuleId($moduleId);

    public function search($conditions, $sort, $start, $limit, $columns = []);

    public function count($conditions);

    public function startAnswer($moduleId, $assessmentId, $userId);

    public function addAssessments($exerciseId, $moduleId, $assessments);

    public function isAssessmentExercise($moduleId, $assessmentId, $exerciseId);
}
