<?php

namespace Biz\Certificate\Service;

interface RecordService
{
    public function get($id);

    public function count($conditions);

    public function search($conditions, $orderBys, $start, $limit, $columns = []);

    public function findExpiredRecords($certificateId);

    public function cancelRecord($id);

    public function grantRecord($id, $fields);

    public function isObtained($conditions);

    public function isCertificatesObtained($userId, $certificateIds);
}
