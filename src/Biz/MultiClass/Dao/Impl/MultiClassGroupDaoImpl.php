<?php

namespace Biz\MultiClass\Dao\Impl;

use Biz\MultiClass\Dao\MultiClassGroupDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MultiClassGroupDaoImpl extends AdvancedDaoImpl implements MultiClassGroupDao
{
    protected $table = 'multi_class_group';

    public function findGroupsByMultiClassId($multiClassId)
    {
        return $this->findByFields([
            'multi_class_id' => $multiClassId,
        ]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time'],
            'orderbys' => ['id', 'created_time'],
            'conditions' => [
                'id = :id',
                'multi_class_id = :multiClassId',
            ],
        ];
    }
}
