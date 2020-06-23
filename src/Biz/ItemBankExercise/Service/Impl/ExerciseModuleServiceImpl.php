<?php

namespace Biz\ItemBankExercise\Service\Impl;

use Biz\BaseService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;

class ExerciseModuleServiceImpl extends BaseService implements ExerciseModuleService
{
    public function findByExerciseId($exerciseId)
    {
        return $this->getItemBankExerciseModuleDao()->findByExerciseId($exerciseId);
    }

    public function get($id)
    {
        return $this->getItemBankExerciseModuleDao()->get($id);
    }

    public function setDefaultAssessmentModule($exerciseId)
    {
        $this->getItemBankExerciseModuleDao()->create([
            'exerciseId' => $exerciseId,
            'title' => '模拟考试',
            'type' => 'assessment'
        ]);
    }

    public function setDefaultChapterModule($exerciseId)
    {
        $this->getItemBankExerciseModuleDao()->create([
            'exerciseId' => $exerciseId,
            'title' => '章节练习',
            'type' => 'chapter'
        ]);
    }

    protected function getItemBankExerciseModuleDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseModuleDao');
    }
}
