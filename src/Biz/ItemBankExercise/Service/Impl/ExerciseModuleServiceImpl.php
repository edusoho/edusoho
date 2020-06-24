<?php

namespace Biz\ItemBankExercise\Service\Impl;

use Biz\BaseService;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;

class ExerciseModuleServiceImpl extends BaseService implements ExerciseModuleService
{
    const ASSESSMENT_MODULE_COUNT = 5;

    public function findByExerciseId($exerciseId)
    {
        return $this->getItemBankExerciseModuleDao()->findByExerciseId($exerciseId);
    }

    public function get($id)
    {
        return $this->getItemBankExerciseModuleDao()->get($id);
    }

    public function createAssessmentModule($exerciseId, $name)
    {
        $this->getItemBankExerciseService()->tryManageExercise($exerciseId);
        $module_count = $this->getItemBankExerciseModuleDao()->count(['exerciseId' => $exerciseId, 'type' => 'assessment']);
        if ($module_count > self::ASSESSMENT_MODULE_COUNT){
            $this->createNewException(ItemBankExerciseException::ASSESSMENT_EXCEED());
        }
        $this->getItemBankExerciseModuleDao()->create([
            'exerciseId' => $exerciseId,
            'title' => $name,
            'type' => 'assessment'
        ]);
    }

    protected function getItemBankExerciseModuleDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseModuleDao');
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }
}
