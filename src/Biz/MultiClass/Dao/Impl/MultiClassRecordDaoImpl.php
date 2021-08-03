<?php

namespace Biz\MultiClass\Dao\Impl;

use Biz\MultiClass\Dao\MultiClassRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MultiClassRecordDaoImpl extends AdvancedDaoImpl implements MultiClassRecordDao
{
    protected $table = 'multi_class_record';

    public function declares()
    {
        return [
            'timestamps' => ['created_time'],
            'orderbys' => ['id', 'created_time'],
            'conditions' => [
                'id = :id',
                'user_id = :userId',
                'multi_class_id = :multiClassId',
            ],
        ];
    }
}
