<?php

namespace Biz\AuditCenter\Service;

interface ContentAuditService
{
    public function getAudit($id);

    public function searchAuditCount($conditions);

    public function searchAudits($conditions, $orderBy, $start, $limit);
}
