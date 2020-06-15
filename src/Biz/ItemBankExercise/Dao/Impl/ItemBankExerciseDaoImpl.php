<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ItemBankExerciseDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ItemBankExerciseDaoImpl extends AdvancedDaoImpl implements ItemBankExerciseDao
{
    protected $table = 'item_bank_exercise';

    public function declares()
    {
        $declares['timestamps'] = [
            'createdTime',
            'updatedTime',
        ];

        $declares['orderbys'] = [
            'id',
            'createdTime',
        ];

        $declares['serializes'] = [
            'teacherIds' => 'delimiter',
            'cover' => 'json',
        ];

        $declares['conditions'] = [
            'id = :id',
            'questionBankId = :questionBankId',
            'categoryId = :categoryId',
            'categoryId IN (:categoryIds)',
            'id IN (:ids)',
            'title like :titleLike',
        ];

        return $declares;
    }
}
