<?php

namespace Biz\Notification\Dao\Impl;

use Biz\Notification\Dao\NotificationStrategyDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class NotificationStrategyDaoImpl extends GeneralDaoImpl implements NotificationStrategyDao
{
    protected $table = 'notification_strategy';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
                'eventId = :eventId',
                'type = :type',
            ),
        );
    }

    public function findByEventId($eventId)
    {
        if (empty($eventId)) {
            return array();
        }

        return $this->findByFields(array('eventId' => $eventId));
    }
}
