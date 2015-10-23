<?php

namespace Topxia\Service\User;

interface BatchNotificationService
{
    public function createBatchNotification($fields);

    public function publishBatchNotification($id);

    public function getBatchNotification($id);

    public function searchBatchNotificationsCount($conditions);

    public function searchBatchNotifications($conditions, $sort, $start, $limit);

    public function checkoutBatchNotification($userId);

    public function deleteBatchNotification($id);

    public function updateBatchNotification($id,$batchNotification);
}