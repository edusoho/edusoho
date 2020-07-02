<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\MemberOperationRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MemberOperationRecordDaoImpl extends AdvancedDaoImpl implements MemberOperationRecordDao
{
    protected $table = 'item_bank_exercise_member_operation_record';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'id = :id',
                'userId IN (:userIds)',
            ],
        ];
    }
}
