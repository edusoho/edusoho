<?php

namespace Biz\AuditCenter\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ContentAuditRecordDao extends AdvancedDaoInterface
{
    public function deleteByAuditId($auditId);
}
