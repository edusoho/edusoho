<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseModuleService
{
    const TYPE_CHAPTER = 'chapter';

    const TYPE_ASSESSMENT = 'assessment';

    const ASSESSMENT_MODULE_COUNT = 5;

    public function findByExerciseId($exerciseId);

    public function findByExerciseIdAndType($exerciseId, $type);

    public function get($id);

    public function createAssessmentModule($exerciseId, $name);

    public function search($conditions, $sort, $start, $limit, $columns = []);

    public function count($conditions);
}
