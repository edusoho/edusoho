<?php

namespace Biz\AuditCenter\Service;

interface ReportAuditService
{
    /**
     * 举报内容审核状态： 未审核
     */
    const STATUS_NONE = 'none_checked';

    /**
     * 举报内容审核状态： 审核为正常
     */
    const STATUS_PASS = 'pass';

    /**
     * 举报内容审核状态： 审核为违规
     */
    const STATUS_ILLEGAL = 'illegal';

    public function searchReportAudits(array $conditions, array $orderBy, $start, $limit, array $columns = []);

    public function searchReportAuditCount(array $conditions);

    public function updateReportAuditStatus($id, $status);

    public function updateReportAuditStatusByIds(array $ids, $status);

    public function getReportAudit($id);

    public function getReportAuditByTargetTypeAndTargetId($targetType, $targetId);

    public function createReportAudit($fields);

    public function updateReportAudit($id, $fields);

    public function deleteReportAudit($id);

    public function deleteReportAuditsByIds($ids);

    public function getReportAuditRecord($id);

    public function createReportAuditRecord($fields);

    public function updateReportAuditRecord($id, $fields);

    public function findReportAuditsByTargetTypeAndTargetId($targetType, $targetId);
}
