<?php

namespace Biz\ItemBankExercise\Service\Impl;

use Biz\BaseService;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;

class AssessmentExerciseServiceImpl extends BaseService implements AssessmentExerciseService
{
    public function search($conditions, $sort, $start, $limit, $columns = [])
    {
        return $this->getItemBankAssessmentExerciseDao()->search($conditions, $sort, $start, $limit, $columns);
    }

    public function count($conditions)
    {
        return $this->getItemBankAssessmentExerciseDao()->count($conditions);
    }

    protected function getItemBankAssessmentExerciseDao()
    {
        return $this->createDao('ItemBankExercise:AssessmentExerciseDao');
    }
}
