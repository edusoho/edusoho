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
        return $this->getItemBankExerciseQuestionRecordDao()->batchCreate($questionRecords);
    }

    public function batchUpdate($ids, $questionRecords)
    {
        return $this->getItemBankExerciseQuestionRecordDao()->batchUpdate($ids, $questionRecords);
    }

    public function deleteByQuestionIds(array $questionIds)
    {
        return $this->getItemBankExerciseQuestionRecordDao()->batchDelete(['questionIds' => $questionIds]);
    }

    public function deleteByItemIds(array $itemIds)
    {
        return $this->getItemBankExerciseQuestionRecordDao()->batchDelete(['itemIds' => $itemIds]);
    }

    protected function getItemBankExerciseQuestionRecordDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseQuestionRecordDao');
    }
}
