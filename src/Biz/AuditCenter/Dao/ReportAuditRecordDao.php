<?php

namespace Biz\AuditCenter\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ReportAuditRecordDao extends AdvancedDaoInterface
{
    public function deleteByAuditId($auditId);
}
