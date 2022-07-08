<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AssessmentExerciseDao extends AdvancedDaoInterface
{
    public function findByModuleId($moduleId);

    public function findByModuleIds($moduleIds);

    public function findByExerciseIdAndModuleId($exerciseId, $moduleId);

    public function isAssessmentExercise($moduleId, $assessmentId, $exerciseId);

    public function getByModuleIdAndAssessmentId($moduleId, $assessmentId);

    public function getAssessmentCountGroupByExerciseId($ids);

    public function getByAssessmentId($assessmentId);

    public function deleteByExerciseId($exerciseId);

    public function deleteByAssessmentId($assessmentId);
}
