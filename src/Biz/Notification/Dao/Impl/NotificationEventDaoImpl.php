<?php

namespace Biz\Notification\Dao\Impl;

use Biz\Notification\Dao\NotificationEventDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class NotificationEventDaoImpl extends AdvancedDaoImpl implements NotificationEventDao
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
            'serializes' => array(
                'reason' => 'json',
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
