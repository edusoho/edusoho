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

    protected function getItemBankExerciseModuleDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseModuleDao');
    }
}
