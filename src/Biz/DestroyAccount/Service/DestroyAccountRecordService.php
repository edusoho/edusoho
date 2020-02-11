<?php

namespace Biz\DestroyAccount\Service;

interface DestroyAccountRecordService
{
    public function getDestroyAccountRecord($id);

    public function updateDestroyAccountRecord($id, $fields);

    public function createDestroyAccountRecord($fields);

    public function deleteDestroyAccountRecord($id);

    public function getLastDestroyAccountRecordByUserId($userId);

    public function searchDestroyAccountRecords($conditions, $orderBy, $start, $limit);
}
