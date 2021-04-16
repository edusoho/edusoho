<?php

namespace Biz\AuditCenter\Service;

interface ReportAuditService
{
    public function searchReportAudits(array $conditions, array $orderBy, $start, $limit, array $columns = []);

    public function searchReportAuditCount(array $conditions);

    public function updateReportAuditStatus($id, $status);

    public function updateReportAuditStatusByIds(array $ids, $status);

    public function getReportAudit($id);

    public function createReportAudit($fields);

    public function updateReportAudit($id, $fields);

    public function deleteReportAudit($id);

    public function searchReportRecords(array $conditions, array $orderBy, $start, $limit, array $columns = []);

    public function searchReportRecordCount(array $conditions);

    public function getReportAuditRecord($id);

    public function createReportAuditRecord($fields);

    public function updateReportAuditRecord($id, $fields);
}
