<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseQuestionRecordService
{
    public function findByUserIdAndModuleId($userId, $moduleId);

    public function batchCreate($questionRecords);

    public function batchUpdate($ids, $questionRecords);
}
