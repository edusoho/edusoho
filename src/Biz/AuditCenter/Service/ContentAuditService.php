<?php

namespace Biz\AuditCenter\Service;

interface ContentAuditService
{
    const AUDIT_STATUS_NONE_CHECKED = 'non_checked';

    const AUDIT_STATUS_PASS = 'pass';

    const AUDIT_STATUS_ILLEGAL = 'illegal';

    public function getAudit($id);

    public function getAuditByTargetTypeAndTargetId($targetType, $targetId);

    public function searchAuditCount($conditions);

    public function searchAudits($conditions, $orderBy, $start, $limit);

    public function confirmUserAudit($id, $status, $auditor);

    public function batchConfirmUserAuditByIds($ids, $status, $auditor);

    public function createAudit($fields);

    public function updateAudit($id, $fields);

    public function deleteAudit($id);

    public function getAuditRecord($id);

    public function createAuditRecord($fields);

    public function updateAuditRecord($id, $fields);

    public function getAuditSetting();
}
