<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ExerciseBindDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ExerciseBindDaoImpl extends AdvancedDaoImpl implements ExerciseBindDao
{
    protected $table = 'item_bank_exercise_bind';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['seq', 'createdTime'],
            'conditions' => [
                'bindId = :bindId',
                'bindType = :bindType',
                'itemBankExerciseId = :itemBankExerciseId'
            ],
        ];
    }
}