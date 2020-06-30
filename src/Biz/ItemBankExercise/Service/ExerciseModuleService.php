<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseModuleService
{
    const TYPE_CHAPTER = 'chapter';

    const TYPE_ASSESSMENT = 'assessment';

    public function findByExerciseId($exerciseId);

    public function findByExerciseIdAndType($exerciseId, $type);

    public function get($id);

    public function createAssessmentModule($exerciseId, $name);

    public function updateAnswerSceneId($moduleId, $answerSceneId);
}
