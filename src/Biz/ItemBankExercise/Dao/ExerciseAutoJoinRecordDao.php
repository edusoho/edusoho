<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ExerciseAutoJoinRecordDao extends GeneralDaoInterface
{
    public function deleteByExerciseId($exerciseId);
}
