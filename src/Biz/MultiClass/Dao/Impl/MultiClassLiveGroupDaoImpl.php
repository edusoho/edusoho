<?php

namespace Biz\MultiClass\Dao\Impl;

use Biz\MultiClass\Dao\MultiClassLiveGroupDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MultiClassLiveGroupDaoImpl extends AdvancedDaoImpl implements MultiClassLiveGroupDao
{
    protected $table = 'multi_class_live_group';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'orderbys' => ['id', 'createdTime'],
            'conditions' => [
                'id = :id',
            ],
        ];
    }
}
