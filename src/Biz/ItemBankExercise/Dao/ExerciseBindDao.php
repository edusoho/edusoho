<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ExerciseBindDao extends AdvancedDaoInterface
{
    public function getBindExercise($bindType, $bindId, $exerciseId);

    public function findBindExerciseByIds($ids);
}
