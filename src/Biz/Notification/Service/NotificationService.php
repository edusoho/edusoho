<?php

namespace Biz\Notification\Service;

interface NotificationService
{
    public function searchBatches($conditions, $orderbys, $start, $limit, $columns = []);

    public function countBatches($conditions);

    public function createBatch($batch);

    public function updateBatch($id, $fields);

    public function getBatch($id);

    public function findEventsByIds($ids);

    public function createEvent($event);

    public function updateEvent($id, $fields);

    public function getEvent($id);

    public function createStrategy($strategy);

    public function batchHandleNotificationResults($batches);

    public function createWeChatNotificationRecord($sn, $key, $data, $source, $batchId = 0);

    public function createSmsNotificationRecord($data, $smsParams, $source, $batchId = 0);
}
