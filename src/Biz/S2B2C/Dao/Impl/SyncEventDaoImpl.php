<?php

namespace Biz\S2B2C\Dao\Impl;

use Biz\S2B2C\Dao\SyncEventDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class SyncEventDaoImpl extends AdvancedDaoImpl implements SyncEventDao
{
    protected $table = 's2b2c_sync_event';

    public function declares()
    {
        return [
            'serializes' => [
                'data' => 'json',
            ],
            'orderbys' => [
                'createdTime',
                'updatedTime',
                'id',
            ],
            'timestamps' => ['createdTime', 'updatedTime'],
            'conditions' => [
                'id = :id',
                'productId = :productId',
                'productId IN (:productIds)',
                'event = :event',
                'event IN (:events)',
                'id IN (:ids)',
                'isConfirm = :isConfirm',
            ],
        ];
    }
}
