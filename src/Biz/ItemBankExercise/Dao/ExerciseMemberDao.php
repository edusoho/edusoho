<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ExerciseMemberDao extends AdvancedDaoInterface
{
    public function getByExerciseIdAndUserIdAndRole($exerciseId, $userId, $role);

    public function findByUserIdAndRole($userId, $role);

    public function findByExerciseIdAndUserId($exerciseId, $userId);

    public function changeMembersDeadlineByExerciseId($exerciseId, $day);

    public function deleteByExerciseId($exerciseId);
}
