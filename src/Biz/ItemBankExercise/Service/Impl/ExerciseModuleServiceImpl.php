<?php

namespace Biz\ItemBankExercise\Service\Impl;

use Biz\BaseService;
use Biz\ItemBankExercise\Dao\ExerciseModuleDao;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;

class ExerciseModuleServiceImpl extends BaseService implements ExerciseModuleService
{
    public function findByExerciseId($exerciseId)
    {
        return $this->getItemBankExerciseModuleDao()->findByExerciseId($exerciseId);
    }

    public function findByExerciseIdAndType($exerciseId, $type)
    {
        return $this->getItemBankExerciseModuleDao()->findByExerciseIdAndType($exerciseId, $type);
    }

    public function get($id)
    {
        return $this->getItemBankExerciseModuleDao()->get($id);
    }

    public function search($conditions, $sort, $start, $limit, $columns = [])
    {
        return $this->getItemBankExerciseModuleDao()->search($conditions, $sort, $start, $limit, $columns);
    }

    public function count($conditions)
    {
        return $this->getItemBankExerciseModuleDao()->count($conditions);
    }

    public function createAssessmentModule($exerciseId, $name)
    {
        $this->getItemBankExerciseService()->tryManageExercise($exerciseId);
        $module_count = $this->getItemBankExerciseModuleDao()->count(['exerciseId' => $exerciseId, 'type' => 'assessment']);
        if ($module_count > self::ASSESSMENT_MODULE_COUNT) {
            $this->createNewException(ItemBankExerciseException::ASSESSMENT_EXCEED());
        }
        $this->getItemBankExerciseModuleDao()->create([
            'exerciseId' => $exerciseId,
            'title' => $name,
            'type' => 'assessment',
        ]);
    }

    /**
     * @return ExerciseModuleDao
     */
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
