<?php

namespace Biz\User\Service;

interface BatchNotificationService
{
    public function createBatchNotification($fields);

    public function publishBatchNotification($id);

    public function getBatchNotification($id);

    public function countBatchNotifications($conditions);

    public function searchBatchNotifications($conditions, $sort, $start, $limit);

    public function checkoutBatchNotification($userId);

    public function deleteBatchNotification($id);

    public function updateBatchNotification($id, $fields);
}
