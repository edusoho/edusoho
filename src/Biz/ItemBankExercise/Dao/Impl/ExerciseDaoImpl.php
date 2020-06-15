<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ExerciseDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ExerciseDaoImpl extends GeneralDaoImpl implements ExerciseDao
{
    protected $table = 'item_bank_exercise';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'id = :id',
            ],
        ];
    }
}
