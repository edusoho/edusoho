<?php

namespace Biz\Notification\Dao\Impl;

use Biz\Notification\Dao\NotificationEventDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class NotificationEventDaoImpl extends GeneralDaoImpl implements NotificationEventDao
{
    protected $table = 'notification_event';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
            ),
        );
    }

    public function findByEventIds($ids)
    {
        if (empty($ids)) {
            return array();
        }

        return $this->findInField('id', $ids);
    }
}
