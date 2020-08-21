<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ExerciseModuleDao extends GeneralDaoInterface
{
    public function findByExerciseId($exerciseId);

    public function findByExerciseIdAndType($exerciseId, $type);

    public function deleteByExerciseId($exerciseId);
}
