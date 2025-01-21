<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ExerciseBindDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ExerciseBindDaoImpl extends AdvancedDaoImpl implements ExerciseBindDao
{
    protected $table = 'item_bank_exercise_bind';

    public function getBindExercise($bindType, $bindId, $exerciseId)
    {
        return $this->getByFields([
            'bindType' => $bindType,
            'bindId' => $bindId,
            'itemBankExerciseId' => $exerciseId,
        ]);
    }

    public function findBindExerciseByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function deleteByExerciseId($exerciseId)
    {
        return $this->db()->delete($this->table(), ['itemBankExerciseId' => $exerciseId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['seq', 'createdTime'],
            'conditions' => [
                'ids in ()',
                'bindId = :bindId',
                'bindType = :bindType',
                'itemBankExerciseId = :itemBankExerciseId',
                'itemBankExerciseId IN (itemBankExerciseIds)',
                'status != statusNotEqual',
            ],
        ];
    }
}
