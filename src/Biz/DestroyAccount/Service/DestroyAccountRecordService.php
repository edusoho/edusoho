<?php

namespace Biz\DestroyAccount\Service;

interface DestroyAccountRecordService
{
    public function getDestroyAccountRecord($id);

    public function updateDestroyAccountRecord($id, $fields);

    public function createDestroyAccountRecord($fields);

    public function deleteDestroyAccountRecord($id);

    public function cancelDestroyAccountRecord();

    public function passDestroyAccountRecord($id);

    public function rejectDestroyAccountRecord($id, $reason);

    public function getLastAuditDestroyAccountRecordByUserId($userId);

    public function searchDestroyAccountRecords($conditions, $orderBy, $start, $limit);

    public function countDestroyAccountRecords($conditions);
}
