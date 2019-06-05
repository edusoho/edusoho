<?php

namespace Biz\Notification\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Notification\Dao\NotificationBatchDao;
use Biz\Notification\Dao\NotificationEventDao;
use Biz\Notification\Service\NotificationService;

class NotificationServiceImpl extends BaseService implements NotificationService
{
    public function searchBatches($conditions, $orderbys, $start, $limit, $columns = array())
    {
        return $this->getNotificationBatchDao()->search($conditions, $orderbys, $start, $limit, $columns);
    }

    public function countBatches($conditions)
    {
        return $this->getNotificationBatchDao()->count($conditions);
    }

    public function getBatch($id)
    {
        return $this->getNotificationBatchDao()->get($id);
    }

    public function createBatch($batch)
    {
        if (!ArrayToolkit::requireds($batch, array('eventId', 'sn', 'strategyId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $batch = ArrayToolkit::parts($batch, array('eventId', 'sn', 'extra', 'strategyId'));

        return $this->getNotificationBatchDao()->create($batch);
    }

    public function findEventsByIds($ids)
    {
        return $this->getNotificationEventDao()->findByEventIds($ids);
    }

    public function createEvent($event)
    {
        if (!ArrayToolkit::requireds($event, array('title', 'content', 'totalCount'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $event = ArrayToolkit::parts($event, array('title', 'content', 'totalCount'));

        return $this->getNotificationEventDao()->create($event);
    }

    /**
     * @return NotificationBatchDao
     */
    protected function getNotificationBatchDao()
    {
        return $this->createDao('Notification:NotificationBatchDao');
    }

    /**
     * @return NotificationEventDao
     */
    protected function getNotificationEventDao()
    {
        return $this->createDao('Notification:NotificationEventDao');
    }
}
