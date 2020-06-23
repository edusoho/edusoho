<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ExerciseDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ExerciseDaoImpl extends GeneralDaoImpl implements ExerciseDao
{
    protected $table = 'item_bank_exercise';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'serializes' => [
                'teacherIds' => 'delimiter',
                'cover' => 'json',
            ],
            'conditions' => [
                'id = :id',
                'questionBankId = :questionBankId',
            ],
        ];
    }
}
