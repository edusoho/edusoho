<?php

namespace Biz\ItemBankExercise\Service\Impl;

use Biz\BaseService;
use Biz\ItemBankExercise\Service\ExerciseQuestionRecordService;

class ExerciseQuestionRecordServiceImpl extends BaseService implements ExerciseQuestionRecordService
{
    public function findByUserIdAndModuleId($userId, $moduleId)
    {
        return $this->getItemBankExerciseQuestionRecordDao()->findByUserIdAndModuleId($userId, $moduleId);
    }

    public function batchCreate($questionRecords)
    {
        $this->getItemBankExerciseQuestionRecordDao()->batchCreate($questionRecords);
    }

    public function batchUpdate($ids, $questionRecords)
    {
        $this->getItemBankExerciseQuestionRecordDao()->batchUpdate($ids, $questionRecords);
    }

    public function getItemBankExerciseQuestionRecordDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseQuestionRecordDao');
    }
}
