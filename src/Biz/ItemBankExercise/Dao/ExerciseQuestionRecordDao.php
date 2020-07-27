<?php

namespace Biz\ItemBankExercise\Dao;

interface ExerciseQuestionRecordDao
{
    public function findByUserIdAndExerciseId($userId, $exerciseId);
}
