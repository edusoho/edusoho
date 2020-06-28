<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseQuestionRecordService
{
    public function findByUserIdAndModuleId($userId, $moduleId);

    public function batchCreate($questionRecords);

    public function batchUpdate($ids, $questionRecords);

    public function deleteByQuestionIds(array $questionIds);

    public function deleteByItemIds(array $itemIds);
}
