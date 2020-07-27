<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ExerciseMemberDaoImpl extends AdvancedDaoImpl implements ExerciseMemberDao
{
    protected $table = 'item_bank_exercise_member';

    public function getByExerciseIdAndUserId($exerciseId, $userId)
    {
        return $this->getByFields([
            'exerciseId' => $exerciseId,
            'userId' => $userId,
        ]);
    }

    public function findByUserIdAndRole($userId, $role)
    {
        return $this->findByFields(['userId' => $userId, 'role' => $role]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime', 'updatedTime', 'deadline', 'doneQuestionNum'],
            'conditions' => [
                'id = :id',
                'id NOT IN (:excludeIds)',
                'id IN (:ids)',
                'userId = :userId',
                'userId IN (:userIds)',
                'exerciseId = :exerciseId',
                'exerciseId IN (:exerciseIds)',
                'role = :role',
                'locked = :locked',
                'doneQuestionNum > :doneQuestionNum',
                'updatedTime >= :startTimeGreaterThan',
                'updatedTime < :startTimeLessThan',
            ],
        ];
    }
}
