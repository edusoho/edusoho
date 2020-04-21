<?php

namespace Biz\S2B2C\Dao\Impl;

use Biz\S2B2C\Dao\ResourceSyncDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ResourceSyncDaoImpl extends GeneralDaoImpl implements ResourceSyncDao
{
    protected $table = 's2b2c_resource_sync';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'conditions' => [
                'id = :id',
            ],
            'orderbys' => ['id'],
        ];
    }
}
