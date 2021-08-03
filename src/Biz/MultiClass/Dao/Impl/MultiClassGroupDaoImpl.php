<?php

namespace Biz\MultiClass\Dao\Impl;

use Biz\MultiClass\Dao\MultiClassGroupDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MultiClassGroupDaoImpl extends AdvancedDaoImpl implements MultiClassGroupDao
{
    protected $table = 'multi_class_group';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time'],
            'orderbys' => ['id', 'created_time'],
            'conditions' => [
                'id = :id',
                'id in (:ids)',
                'multi_class_id = :multiClassId',
            ],
        ];
    }

}
