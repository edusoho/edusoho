<?php

namespace Biz\Notification\Dao\Impl;

use Biz\Notification\Dao\NotificationBatchDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class NotificationBatchDaoImpl extends AdvancedDaoImpl implements NotificationBatchDao
{
    protected $table = 'notification_batch';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
            'conditions' => [
                'id = :id',
                'eventId = :eventId',
                'sn = :sn',
                'status = :status',
                'strategyId = :strategyId',
                'source = :source',
            ],
        ];
    }
}
