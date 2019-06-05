<?php

namespace Biz\Notification\Service;

interface NotificationService
{
    public function searchBatches($conditions, $orderbys, $start, $limit, $columns = array());

    public function countBatches($conditions);

    public function createBatch($batch);

    public function getBatch($id);

    public function findEventsByIds($ids);

    public function createEvent($event);
}
