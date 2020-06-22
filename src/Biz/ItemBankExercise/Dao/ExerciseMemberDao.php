<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ExerciseMemberDao extends GeneralDaoInterface
{
    public function getByExerciseIdAndUserId($exerciseId, $userId);
}
