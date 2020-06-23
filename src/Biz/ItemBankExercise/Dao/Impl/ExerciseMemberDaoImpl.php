<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ExerciseMemberDaoImpl extends GeneralDaoImpl implements ExerciseMemberDao
{
    protected $table = 'item_bank_exercise_member';

    public function getByExerciseIdAndUserId($exerciseId, $userId)
    {
        return $this->getByFields(array(
            'exerciseId' => $exerciseId,
            'userId' => $userId,
        ));
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'id = :id',
                'id NOT IN (:excludeIds)',
                'userId = :userId',
                'exerciseId = :exerciseId',
                'role = :role',
            ],
        ];
    }
}
