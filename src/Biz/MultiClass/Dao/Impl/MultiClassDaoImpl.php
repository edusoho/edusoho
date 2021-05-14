<?php

namespace Biz\MultiClass\Dao\Impl;

use Biz\MultiClass\Dao\MultiClassDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MultiClassDaoImpl extends GeneralDaoImpl implements MultiClassDao
{
    protected $table = 'multi_class';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
            'conditions' => [
                'id = :id',
            ],
        ];
    }
}
