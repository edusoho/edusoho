<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ExerciseMemberDao extends AdvancedDaoInterface
{
    public function getByExerciseIdAndUserId($exerciseId, $userId);

    public function findByUserIdAndRole($userId, $role);

    public function deleteByExerciseId($exerciseId);
}
