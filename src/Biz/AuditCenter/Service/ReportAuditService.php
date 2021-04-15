<?php

namespace Biz\AuditCenter\Service;

interface ReportAuditService
{
    public function getReportAudit($id);

    public function createReportAudit($fields);

    public function updateReportAudit($id, $fields);

    public function deleteReportAudit($id);

    public function getReportAuditRecord($id);

    public function createReportAuditRecord($fields);

    public function updateReportAuditRecord($id, $fields);
}
