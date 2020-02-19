<?php

namespace Biz\DestroyAccount\Service;

use Biz\System\Annotation\Log;

interface DestroyAccountRecordService
{
    public function getDestroyAccountRecord($id);

    public function updateDestroyAccountRecord($id, $fields);

    /**
     * @param $fields
     *
     * @return mixed
     * @Log(module="destroy_account_record", action="create")
     */
    public function createDestroyAccountRecord($fields);

    public function deleteDestroyAccountRecord($id);

    public function cancelDestroyAccountRecord();

    public function passDestroyAccountRecord($id);

    public function rejectDestroyAccountRecord($id, $reason);

    public function getLastAuditDestroyAccountRecordByUserId($userId);

    public function searchDestroyAccountRecords($conditions, $orderBy, $start, $limit);

    public function countDestroyAccountRecords($conditions);
}
