<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseModuleService
{
    const TYPE_CHAPTER = 'chapter';

    const TYPE_ASSESSMENT = 'assessment';

    const ASSESSMENT_MODULE_COUNT = 5;

    const ASSESSMENT_MODULE_LEAST_COUNT = 1;

    public function findByExerciseId($exerciseId);

    public function findByExerciseIdAndType($exerciseId, $type);

    public function get($id);

    public function createAssessmentModule($exerciseId, $name);

    public function updateAssessmentModule($moduleId, $fields);

    public function deleteAssessmentModule($moduleId);

    public function search($conditions, $sort, $start, $limit, $columns = []);

    public function count($conditions);
}
