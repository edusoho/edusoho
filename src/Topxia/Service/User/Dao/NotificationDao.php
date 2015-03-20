<?php

namespace Topxia\Service\User\Dao;

interface NotificationDao
{
	public function addNotification($notification);

	public function findNotificationsByUserId($userId, $start, $limit);

    public function getNotificationCountByUserId($userId);

    public function updateNotification($id, $fields);
}