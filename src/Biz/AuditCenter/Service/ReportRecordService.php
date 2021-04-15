<?php

namespace Biz\AuditCenter\Service;

interface ReportRecordService
{
    public function getReportRecord($id);

    public function createReportRecord($fields);

    public function updateReportRecord($id, $fields);

    public function deleteReportRecord($id);

    public function searchReportRecords($conditions, $orderBys, $start, $limit, $columns = []);
}
