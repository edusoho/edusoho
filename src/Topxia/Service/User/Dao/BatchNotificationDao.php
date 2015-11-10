<?php

namespace Topxia\Service\User\Dao;

interface BatchNotificationDao
{
    public function addBatchNotification($batchNotification);

	public function getBatchNotification($id);

	public function searchBatchNotificationCount($conditions);

	public function searchBatchNotifications($conditions, $orderBy, $start, $limit);

	public function deleteBatchNotification($id);

	public function updateBatchNotification($id,$batchNotification);
}