<?php

namespace Biz\ItemBankExercise\Service\Impl;

use Biz\BaseService;
use Biz\ItemBankExercise\Dao\ExerciseDao;
use Biz\ItemBankExercise\Service\ExerciseService;

class ExerciseServiceImpl extends BaseService implements ExerciseService
{
    public function count($conditions)
    {
        return $this->getItemBankExerciseDao()->count($conditions);
    }

    public function search($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getItemBankExerciseDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function get($id)
    {
        return $this->getItemBankExerciseDao()->get($id);
    }

    // todo
    public function canLearningExercise($exerciseId, $userId)
    {
        return true;
    }

    protected function _prepareCourseConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if (0 == $value) {
                return true;
            }

            return !empty($value);
        });

        return $conditions;
    }

    /**
     * @return ExerciseDao
     */
    protected function getItemBankExerciseDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseDao');
    }
}
