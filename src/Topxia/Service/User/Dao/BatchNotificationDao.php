<?php

namespace Topxia\Service\User\Dao;

interface BatchNotificationDao
{
    public function addBatchNotification($batchNotification);

	public function getBatchNotificationById($id);

	public function searchBatchNotificationCount($conditions);

	public function searchBatchNotifications($conditions, $orderBy, $start, $limit);
}