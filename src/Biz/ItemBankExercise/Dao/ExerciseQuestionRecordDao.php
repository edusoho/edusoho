<?php

namespace Biz\ItemBankExercise\Dao;

interface ExerciseQuestionRecordDao
{
    public function findByUserIdAndExerciseId($userId, $exerciseId);

    public function deleteByExerciseId($exerciseId);

    public function countQuestionRecordStatus($exerciseId, $itemIds);
}
