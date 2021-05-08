<?php

namespace Biz\AuditCenter\Service;

interface ReportRecordService
{
    public function getReportRecord($id);

    public function getUserReportRecordByTargetTypeAndTargetId($userId, $targetType, $targetId);

    public function getReportRecordByAuditIdAndReporter($auditId, $reporter);

    public function createReportRecord($fields);

    public function updateReportRecord($id, $fields);

    public function findUserReportRecordsByTargetTypeAndTargetIds($userId, $targetType, array $targetIds);

    public function deleteReportRecord($id);

    public function searchReportRecords(array $conditions, array $orderBy, $start, $limit, array $columns = []);

    public function searchReportRecordCount(array $conditions);
}
