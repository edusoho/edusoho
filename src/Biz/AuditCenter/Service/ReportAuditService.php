<?php

namespace Biz\AuditCenter\Service;

interface ReportAuditService
{
    public function searchReportAudits(array $conditions, array $orderBy, $start, $limit, array $columns = []);

    public function searchReportAuditCount(array $conditions);

    public function updateReportAuditStatus($id, $status);

    public function updateReportAuditStatusByIds(array $ids, $status);
}
