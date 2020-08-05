<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseQuestionRecordService
{
    public function findByUserIdAndExerciseId($userId, $exerciseId);

    public function batchCreate($questionRecords);

    public function batchUpdate($ids, $questionRecords);

    public function deleteByQuestionIds(array $questionIds);

    public function deleteByItemIds(array $itemIds);

    public function updateByAnswerRecordIdAndModuleId($answerRecordId, $moduleId);
}
