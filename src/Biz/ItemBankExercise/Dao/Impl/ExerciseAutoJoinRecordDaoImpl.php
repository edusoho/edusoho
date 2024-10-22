<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ExerciseAutoJoinRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ExerciseAutoJoinRecordDaoImpl extends AdvancedDaoImpl implements ExerciseAutoJoinRecordDao
{
    protected $table = 'item_ban_exercise_auto_join_record';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'userId = :userId',
                'itemBankExerciseId IN (:itemBankExerciseIds)',
                'itemBankExerciseId = :itemBankExerciseId',
                'itemBankExerciseBindId = :itemBankExerciseBindId',
            ],
        ];
    }
}
