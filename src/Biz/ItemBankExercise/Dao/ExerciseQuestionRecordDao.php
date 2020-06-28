<?php

namespace Biz\ItemBankExercise\Dao;

interface ExerciseQuestionRecordDao
{
    public function findByUserIdAndModuleId($userId, $moduleId);
}
