<?php

namespace Biz\S2B2C\Dao\Impl;

use Biz\S2B2C\Dao\ResourceSyncLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ResourceSyncLogDaoImpl extends GeneralDaoImpl implements ResourceSyncLogDao
{
    protected $table = 's2b2c_resource_sync_log';

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
