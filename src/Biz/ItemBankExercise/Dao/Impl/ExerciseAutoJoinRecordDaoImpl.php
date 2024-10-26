<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ExerciseAutoJoinRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ExerciseAutoJoinRecordDaoImpl extends AdvancedDaoImpl implements ExerciseAutoJoinRecordDao
{
    protected $table = 'item_bank_exercise_auto_join_record';

    public function deleteByExerciseId($exerciseId)
    {
        return $this->db()->delete($this->table(), ['itemBankExerciseId' => $exerciseId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'userId = :userId',
                'userId IN (:userIds)',
                'itemBankExerciseId IN (:itemBankExerciseIds)',
                'itemBankExerciseId = :itemBankExerciseId',
                'itemBankExerciseBindId = :itemBankExerciseBindId',
                'itemBankExerciseBindId IN (:itemBankExerciseBindIds)',
            ],
        ];
    }
}
